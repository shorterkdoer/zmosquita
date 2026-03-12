<?php
namespace App\Controllers;

use App\Core\Controller;
use Foundation\Core\Request;
use Foundation\Core\Response;
use Foundation\Core\Session;
use Foundation\Core\CSRF;
use App\Services\AuthService;
use App\Services\EmailService;
use App\Services\MatriculaService;
use App\Services\TramiteService;
use App\Support\Sanitizer;
use Gregwar\Captcha\PhraseBuilder;

/**
 * AuthController - Refactored to use Service Layer
 *
 * This controller now delegates business logic to services:
 * - AuthService: login, register, logout, password reset
 * - MatriculaService: matricula status and operations
 * - EmailService: email sending
 */
class AuthController extends Controller
{
    protected AuthService $auth;
    protected EmailService $emails;
    protected MatriculaService $matriculas;

    public function __construct()
    {
        // Initialize services
        $this->emails = new EmailService();
        $tramites = new TramiteService($this->emails);
        $this->matriculas = new MatriculaService($tramites, $this->emails);
        $this->auth = new AuthService($this->emails);
    }

    /**
     * Process user login
     */
    public function logged(): void
    {
        // Validate CSRF
        try {
            CSRF::validateOrFail();
        } catch (\RuntimeException $e) {
            Session::flash('error', 'Error de validación del formulario. Por favor, recargue e intente nuevamente.');
            $this->redirect('/login');
            return;
        }

        // Validate CAPTCHA
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['phrase']) || !PhraseBuilder::comparePhrases($_SESSION['phrase'], $_POST['phrase'] ?? '')) {
            Session::flash('error', 'Captcha no válido');
            $this->redirect('/login');
            return;
        }

        $phase2 = $_POST['phrase2'] ?? '';
        if ($phase2 !== '') {
            Session::flash('error', 'Recargue el formulario y pruebe nuevamente.');
            $this->redirect('/login');
            return;
        }

        // Get credentials
        $email = Sanitizer::email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Use AuthService to login
        $result = $this->auth->login($email, $password);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/login');
            return;
        }

        // Redirect based on role
        $redirectUrl = $result['user']['role'] === 'admin'
            ? '/admin-dashboard'
            : '/user-dashboard';

        Response::redirect($redirectUrl);
    }

    /**
     * Process revision request (solicitar revisión de matrícula)
     */
    public function procedurevision(): void
    {
        // Validate CSRF
        try {
            CSRF::validateOrFail();
        } catch (\RuntimeException $e) {
            Session::flash('error', 'Error de validación del formulario. Por favor, recargue e intente nuevamente.');
            $this->redirect('/arevision');
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Validate CAPTCHA
        if (!isset($_SESSION['phrase']) || !PhraseBuilder::comparePhrases($_SESSION['phrase'], $_POST['phrase'] ?? '')) {
            Session::flash('error', 'Captcha no válido');
            $this->redirect('/arevision');
            return;
        }

        $phase2 = $_POST['phrase2'] ?? '';
        if ($phase2 !== '') {
            Session::flash('error', 'Recargue el formulario y pruebe nuevamente.');
            $this->redirect('/arevision');
            return;
        }

        $userId = $_SESSION['user']['id'] ?? 0;
        if ($userId === 0) {
            Session::flash('error', 'Usuario no autenticado.');
            $this->redirect('/login');
            return;
        }

        // Use MatriculaService to request revision
        $result = $this->matriculas->solicitarRevision($userId);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/user-dashboard');
            return;
        }

        Session::flash('Success', 'Revise su mail. Se recibió su solicitud.');
        Response::redirect('/user-dashboard');
    }

    /**
     * Show login form
     */
    public function loginForm(): void
    {
        $this->view('auth/login', [
            'error' => $_GET['error'] ?? null
        ]);
    }

    /**
     * Show revision request form
     */
    public function piderevision(): void
    {
        $this->view('auth/pedirrevision', [
            'error' => $_GET['error'] ?? null
        ]);
    }

    /**
     * Show registration form
     */
    public function registerForm(): void
    {
        $this->view('auth/register', [
            'error' => Session::flash('error', '')
        ]);
    }

    /**
     * Process user registration
     */
    public function register(): void
    {
        // Validate CSRF
        try {
            CSRF::validateOrFail();
        } catch (\RuntimeException $e) {
            Session::flash('error', 'Error de validación del formulario. Por favor, recargue e intente nuevamente.');
            $this->redirect('/register');
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Validate CAPTCHA
        if (!isset($_SESSION['phrase']) || !PhraseBuilder::comparePhrases($_SESSION['phrase'], $_POST['phrase'] ?? '')) {
            unset($_SESSION['phrase']);
            Session::flash('error', 'Captcha no válido');
            $this->redirect('/register');
            return;
        }
        unset($_SESSION['phrase']);

        // Validate password confirmation
        if (trim($_POST['verdura'] ?? '') !== trim($_POST['frutita'] ?? '')) {
            Session::flash('error', 'Las contraseñas no coinciden.');
            $this->redirect('/register');
            return;
        }

        // Validate math captcha
        if (!isset($_SESSION['preverif'])) {
            Session::flash('error', 'Reinicie la operación!');
            $this->redirect('/register');
            return;
        }

        $quest01 = (int)($_POST["vari_l"] ?? 0);
        if ($quest01 - $_SESSION['preverif'] !== 0) {
            Session::flash('error', 'Respuesta incorrecta!');
            $this->redirect('/register');
            return;
        }

        $phase2 = trim($_POST['phrase2'] ?? '');
        if ($phase2 !== '') {
            Session::flash('error', 'Recargue el formulario y pruebe nuevamente.');
            $this->redirect('/register');
            return;
        }

        // Get registration data
        $email = Sanitizer::email(trim($_POST['emilio'] ?? ''));
        $password = trim($_POST['frutita'] ?? '');

        if (empty($email) || empty($password)) {
            Session::flash('error', 'Email y contraseña son obligatorios.');
            $this->redirect('/register');
            return;
        }

        // Use AuthService to register
        $result = $this->auth->register($email, $password);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/register');
            return;
        }

        Session::flash('error', 'Registro sujeto a revisión. Recibirá un mail de activación y luego podrá iniciar sesión.');
        $this->redirect('/login');
    }

    /**
     * Admin accepts registration request and sends activation email
     */
    public function aceptarsolicitud(string $email): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SESSION['user']['role'] !== 'admin') {
            Session::flash('error', 'No tiene permisos para realizar esta acción.');
            $this->redirect('/login');
            return;
        }

        $aspirante = \App\Models\User::findByEmail($email);

        if (!$aspirante) {
            Session::flash('error', 'Usuario no encontrado.');
            $this->redirect('/login');
            return;
        }

        // Send activation email
        if (!$this->emails->sendActivation($email, $aspirante['activation_token'])) {
            Session::flash('error', 'No se pudo enviar el correo de activación.');
            $this->redirect('/login');
            return;
        }

        Session::flash('success', 'Email de activación enviado a ' . $email);
        $this->redirect('/admin-dashboard');
    }

    /**
     * Process user logout
     */
    public function logout(): void
    {
        $this->auth->logout();
        $this->redirect('/login');
    }

    /**
     * Activate user account using token
     */
    public function activateAccount(Request $request, array $params = []): void
    {
        $token = $params[0] ?? null;

        if (!$token) {
            Session::flash('error', 'Token no proporcionado.');
            $this->redirect('/login');
            return;
        }

        // Use AuthService to activate
        $result = $this->auth->activate($token);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/user-dashboard');
            return;
        }

        Session::flash('info', 'Cuenta activada correctamente. Ahora puede iniciar sesión.');
        $this->redirect('/user-dashboard');
    }

    /**
     * Show forgot password form
     */
    public function forgotForm(): void
    {
        $this->view('auth/forgot-password', [
            'error' => Session::flash('error', ''),
            'message' => Session::flash('success', ''),
        ]);
    }

    /**
     * Process forgot password request
     */
    public function sendForgotPassword(): void
    {
        // Validate CSRF
        try {
            CSRF::validateOrFail();
        } catch (\RuntimeException $e) {
            Session::flash('error', 'Error de validación del formulario. Por favor, recargue e intente nuevamente.');
            Response::redirect('/password/forgot');
            return;
        }

        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Email inválido');
            Response::redirect('/password/forgot');
            return;
        }

        // Use AuthService to request password reset
        $result = $this->auth->requestPasswordReset($email);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            Response::redirect('/password/forgot');
            return;
        }

        Session::flash('success', 'Si el email existe, recibirás un enlace para resetear tu contraseña.');
        Response::redirect('/login');
    }

    /**
     * Show reset password form
     */
    public function resetPasswordForm(Request $request, array $params = []): void
    {
        $token = $params[0] ?? null;

        if (!$token) {
            Session::flash('error', 'Token no proporcionado.');
            Response::redirect('/password/forgot');
            return;
        }

        $user = \App\Models\User::findByResetToken($token);

        if (!$user) {
            Session::flash('error', 'Enlace inválido o expirado.');
            Response::redirect('/password/forgot');
            return;
        }

        $this->view('auth/reset-password', [
            'token' => $token,
            'error' => Session::flash('error', '')
        ]);
    }

    /**
     * Process password reset
     */
    public function resetPassword(): void
    {
        // Validate CSRF
        try {
            CSRF::validateOrFail();
        } catch (\RuntimeException $e) {
            Session::flash('error', 'Error de validación del formulario. Por favor, recargue e intente nuevamente.');
            Response::redirect('/login');
            return;
        }

        $token = trim($_POST['token'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $passwordConfirmation = trim($_POST['password_confirmation'] ?? '');

        if ($password !== $passwordConfirmation) {
            Session::flash('error', 'Las contraseñas no coinciden.');
            Response::redirect('/login');
            return;
        }

        if (strlen($password) < 6) {
            Session::flash('error', 'La contraseña debe tener al menos 6 caracteres.');
            Response::redirect('/login');
            return;
        }

        // Use AuthService to reset password
        $result = $this->auth->resetPassword($token, $password);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            Response::redirect('/login');
            return;
        }

        Session::flash('success', 'Contraseña actualizada correctamente. Ahora puedes iniciar sesión.');
        Response::redirect('/login');
    }

    /**
     * Show requisitos page
     */
    public function requisitos(): void
    {
        $this->view('sitio/requisitos', [
            'error' => Session::flash('error', '')
        ]);
    }

    /**
     * Show institutional page
     */
    public function institucional(): void
    {
        $this->view('sitio/comisiones', [
            'error' => Session::flash('error', '')
        ]);
    }

    /**
     * Show user dashboard
     */
    public function showUserDashboard(): void
    {
        // Check if user is authenticated
        if (!$this->auth->isAuthenticated()) {
            Session::flash('error', 'Debes iniciar sesión para acceder al dashboard.');
            Response::redirect('/login');
            return;
        }

        // Get current user
        $user = $this->auth->currentUser();
        if (!$user) {
            Session::flash('error', 'Sesión inválida. Por favor, inicia sesión nuevamente.');
            Response::redirect('/login');
            return;
        }

        // Render dashboard with user data
        $this->view('dashboard/user', [
            'user' => $user
        ]);
    }

    /**
     * Show admin dashboard
     */
    public function showAdminDashboard(): void
    {
        // Check if user is authenticated
        if (!$this->auth->isAuthenticated()) {
            Session::flash('error', 'Debes iniciar sesión para acceder al dashboard.');
            Response::redirect('/login');
            return;
        }

        // Check if user has admin role
        if (!$this->auth->hasRole('admin')) {
            Session::flash('error', 'No tienes permisos para acceder al panel administrativo.');
            Response::redirect('/user-dashboard');
            return;
        }

        // Get current user
        $user = $this->auth->currentUser();
        if (!$user) {
            Session::flash('error', 'Sesión inválida. Por favor, inicia sesión nuevamente.');
            Response::redirect('/login');
            return;
        }

        // Render admin dashboard with user data
        $this->view('dashboard/admin', [
            'user' => $user
        ]);
    }

    /**
     * Send revision notification emails (helper method)
     * @deprecated Use EmailService directly
     */
    protected function sendRevisionEmail(string $email): bool
    {
        // Get user data for notification
        $datos = \App\Models\DatosPersonales::findByUserId($_SESSION['user']['id'] ?? 0);
        $nombre = 'Usuario';
        if ($datos) {
            $nombre = ucwords(($datos['apellido'] ?? '') . ', ' . ($datos['nombre'] ?? ''));
        }

        return $this->emails->sendRevisionNotification($email, $nombre);
    }

    /**
     * Notify admin about revision (helper method)
     * @deprecated Use EmailService directly
     */
    protected function NotifyRevisionEmail(string $userEmail): bool
    {
        $adminEmail = 'admin@coprobilp.org.ar'; // Get from config
        return $this->emails->notifyAdminRevision($adminEmail, 'Usuario', $userEmail);
    }
}
