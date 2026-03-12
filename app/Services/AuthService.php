<?php
namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\DatosPersonalesRepository;
use App\Repositories\MatriculaRepository;
use Foundation\Core\Session;
use Foundation\Core\CSRF;

/**
 * AuthService - Handles authentication business logic
 *
 * Refactored to use Repository Pattern for data access
 */
class AuthService
{
    protected EmailService $emails;
    protected UserRepository $userRepo;
    protected DatosPersonalesRepository $datosPersonalesRepo;
    protected MatriculaRepository $matriculaRepo;

    public function __construct(
        EmailService $emails,
        ?UserRepository $userRepo = null,
        ?DatosPersonalesRepository $datosPersonalesRepo = null,
        ?MatriculaRepository $matriculaRepo = null
    ) {
        $this->emails = $emails;
        $this->userRepo = $userRepo ?? new UserRepository();
        $this->datosPersonalesRepo = $datosPersonalesRepo ?? new DatosPersonalesRepository();
        $this->matriculaRepo = $matriculaRepo ?? new MatriculaRepository();
    }

    /**
     * Authenticate user with email and password
     *
     * @param string $email User email
     * @param string $password User password
     * @return array ['success' => bool, 'error' => string|null, 'user' => array|null]
     */
    public function login(string $email, string $password): array
    {
        $user = $this->userRepo->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            return [
                'success' => false,
                'error' => 'Credenciales inválidas.',
                'user' => null
            ];
        }

        if (!$user['active']) {
            return [
                'success' => false,
                'error' => 'Cuenta no activada. Por favor, revise su email.',
                'user' => null
            ];
        }

        // Regenerate session to prevent session fixation
        Session::regenerate();
        CSRF::regenerate();

        // Store user data in session
        $sessionUser = [
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        Session::set('user', $sessionUser);

        return [
            'success' => true,
            'error' => null,
            'user' => $sessionUser
        ];
    }

    /**
     * Register new user
     *
     * @param string $email User email
     * @param string $password User password
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function register(string $email, string $password): array
    {
        // Check if email already exists
        $existing = $this->userRepo->findByEmail($email);
        if ($existing) {
            return [
                'success' => false,
                'error' => 'El email ya está registrado.'
            ];
        }

        // Generate activation token
        $token = bin2hex(random_bytes(16));
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Create user
        $userId = $this->userRepo->create([
            'email' => $email,
            'password' => $hash,
            'activation_token' => $token,
            'active' => 0,
            'role' => 'user',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if (!$userId) {
            return [
                'success' => false,
                'error' => 'Error al crear usuario. Intente nuevamente.'
            ];
        }

        // Send activation email
        if (!$this->emails->sendActivation($email, $token)) {
            return [
                'success' => false,
                'error' => 'Usuario creado pero error enviando email de activación.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Logout current user
     */
    public function logout(): void
    {
        Session::clear();
    }

    /**
     * Activate user account using token
     *
     * @param string $token Activation token
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function activate(string $token): array
    {
        $user = $this->userRepo->findByActivationToken($token);

        if (!$user) {
            return [
                'success' => false,
                'error' => 'Token de activación inválido.'
            ];
        }

        // Activate user
        if (!$this->userRepo->activate($user['id'])) {
            return [
                'success' => false,
                'error' => 'Error activando la cuenta.'
            ];
        }

        // Create related records (datos personales and matricula)
        $this->createUserRecords($user['id']);

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Request password reset
     *
     * @param string $email User email
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function requestPasswordReset(string $email): array
    {
        $user = $this->userRepo->findByEmail($email);

        // Don't reveal if email exists or not (security)
        if (!$user) {
            return [
                'success' => true,
                'error' => null
            ];
        }

        $token = bin2hex(random_bytes(16));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

        if (!$this->userRepo->setPasswordResetToken($email, $token, $expires)) {
            return [
                'success' => false,
                'error' => 'Error generando token de recuperación.'
            ];
        }

        if (!$this->emails->sendPasswordReset($email, $token)) {
            return [
                'success' => false,
                'error' => 'Error enviando email de recuperación.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Reset password using token
     *
     * @param string $token Password reset token
     * @param string $newPassword New password
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function resetPassword(string $token, string $newPassword): array
    {
        $user = $this->userRepo->findByResetToken($token);

        if (!$user) {
            return [
                'success' => false,
                'error' => 'Token inválido o expirado.'
            ];
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);

        if (!$this->userRepo->updatePassword($user['id'], $hash)) {
            return [
                'success' => false,
                'error' => 'Error actualizando la contraseña.'
            ];
        }

        if (!$this->userRepo->clearPasswordResetToken($user['id'])) {
            return [
                'success' => false,
                'error' => 'Error limpiando token de recuperación.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return Session::isAuthenticated();
    }

    /**
     * Get current authenticated user
     */
    public function currentUser(): ?array
    {
        return Session::user();
    }

    /**
     * Get current user's role
     */
    public function getCurrentRole(): ?string
    {
        return Session::getRole();
    }

    /**
     * Check if current user has specific role
     */
    public function hasRole(string $role): bool
    {
        return Session::hasRole($role);
    }

    /**
     * Create related records for new user (datos personales and matricula)
     */
    protected function createUserRecords(int $userId): void
    {
        // Create empty datospersonales record
        $this->datosPersonalesRepo->createForUser($userId);

        // Create empty matriculas record
        $this->matriculaRepo->create(['user_id' => $userId]);
    }
}
