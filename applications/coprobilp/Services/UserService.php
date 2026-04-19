<?php
namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\DatosPersonalesRepository;
use App\Repositories\MatriculaRepository;

/**
 * UserService - Handles user management business logic
 *
 * Refactored to use Repository Pattern for data access
 */
class UserService
{
    protected UserRepository $userRepo;
    protected DatosPersonalesRepository $datosPersonalesRepo;
    protected MatriculaRepository $matriculaRepo;

    public function __construct(
        ?UserRepository $userRepo = null,
        ?DatosPersonalesRepository $datosPersonalesRepo = null,
        ?MatriculaRepository $matriculaRepo = null
    ) {
        $this->userRepo = $userRepo ?? new UserRepository();
        $this->datosPersonalesRepo = $datosPersonalesRepo ?? new DatosPersonalesRepository();
        $this->matriculaRepo = $matriculaRepo ?? new MatriculaRepository();
    }
    /**
     * Create a new user (admin function)
     *
     * @param array $data User data
     * @return array ['success' => bool, 'error' => string|null, 'user_id' => int|null]
     */
    public function create(array $data): array
    {
        // Validate email
        if (empty($data['email'])) {
            return [
                'success' => false,
                'error' => 'Email es obligatorio.',
                'user_id' => null
            ];
        }

        // Check if email exists
        $existing = $this->userRepo->findByEmail($data['email']);
        if ($existing) {
            return [
                'success' => false,
                'error' => 'El email ya está registrado.',
                'user_id' => null
            ];
        }

        // Hash password
        if (empty($data['password'])) {
            $data['password'] = bin2hex(random_bytes(8)); // Generate random password
        }
        $hash = password_hash($data['password'], PASSWORD_DEFAULT);

        // Create user
        $userData = [
            'email' => $data['email'],
            'password' => $hash,
            'role' => $data['role'] ?? 'user',
            'active' => $data['active'] ?? 1,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $userId = $this->userRepo->create($userData);

        if (!$userId) {
            return [
                'success' => false,
                'error' => 'Error al crear usuario.',
                'user_id' => null
            ];
        }

        // Create related records
        $this->datosPersonalesRepo->createForUser($userId);
        $this->matriculaRepo->create(['user_id' => $userId]);

        return [
            'success' => true,
            'error' => null,
            'user_id' => $userId
        ];
    }

    /**
     * Update user data
     *
     * @param int $userId User ID
     * @param array $data Data to update
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function update(int $userId, array $data): array
    {
        // Don't allow email change to existing email
        if (isset($data['email'])) {
            $existing = $this->userRepo->findByEmail($data['email']);
            if ($existing && $existing['id'] != $userId) {
                return [
                    'success' => false,
                    'error' => 'El email ya está en uso por otro usuario.'
                ];
            }
        }

        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }

        if (!$this->userRepo->update($userId, $data)) {
            return [
                'success' => false,
                'error' => 'Error al actualizar usuario.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Delete user
     *
     * @param int $userId User ID
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function delete(int $userId): array
    {
        // Check if user has active matricula
        $matricula = $this->matriculaRepo->findByUserId($userId);
        if ($matricula && !empty($matricula['aprobado']) && empty($matricula['baja'])) {
            return [
                'success' => false,
                'error' => 'No se puede eliminar un usuario con matrícula activa. Dar de baja primero.'
            ];
        }

        if (!$this->userRepo->delete($userId)) {
            return [
                'success' => false,
                'error' => 'Error al eliminar usuario.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Update user role
     *
     * @param int $userId User ID
     * @param string $role New role
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function updateRole(int $userId, string $role): array
    {
        if (!in_array($role, ['admin', 'user'])) {
            return [
                'success' => false,
                'error' => 'Rol inválido.'
            ];
        }

        if (!$this->userRepo->updateRole($userId, $role)) {
            return [
                'success' => false,
                'error' => 'Error actualizando rol.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Get user by ID
     *
     * @param int $userId User ID
     * @return array|null User data or null
     */
    public function findById(int $userId): ?array
    {
        return $this->userRepo->find($userId);
    }

    /**
     * Get user by email
     *
     * @param string $email User email
     * @return array|null User data or null
     */
    public function findByEmail(string $email): ?array
    {
        return $this->userRepo->findByEmail($email);
    }

    /**
     * Get all users
     *
     * @param array $filters Optional filters ['role' => string, 'active' => bool]
     * @return array Users
     */
    public function getAll(array $filters = []): array
    {
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];

        if (isset($filters['role'])) {
            $sql .= " AND role = :role";
            $params[':role'] = $filters['role'];
        }

        if (isset($filters['active'])) {
            $sql .= " AND active = :active";
            $params[':active'] = $filters['active'] ? 1 : 0;
        }

        $sql .= " ORDER BY created_at DESC";

        return $this->userRepo->query($sql, $params);
    }

    /**
     * Get pending users (not activated)
     *
     * @return array Pending users
     */
    public function getPending(): array
    {
        return $this->userRepo->getPending();
    }

    /**
     * Get users with matriculas
     *
     * @param array $filters Optional filters
     * @return array Users with matricula data
     */
    public function getWithMatricula(array $filters = []): array
    {
        $sql = "SELECT u.id, u.email, u.role, u.active,
                       m.matriculaasignada, m.aprobado, m.baja, m.estado,
                       dp.nombre, dp.apellido
                FROM users u
                LEFT JOIN matriculas m ON u.id = m.user_id
                LEFT JOIN datospersonales dp ON u.id = dp.user_id
                WHERE 1=1";

        $params = [];

        if (isset($filters['has_matricula'])) {
            if ($filters['has_matricula']) {
                $sql .= " AND m.matriculaasignada IS NOT NULL";
            } else {
                $sql .= " AND m.matriculaasignada IS NULL";
            }
        }

        if (isset($filters['estado'])) {
            $sql .= " AND m.estado = :estado";
            $params[':estado'] = $filters['estado'];
        }

        $sql .= " ORDER BY u.created_at DESC";

        return $this->userRepo->query($sql, $params);
    }

    /**
     * Search users by name or email
     *
     * @param string $query Search query
     * @return array Matching users
     */
    public function search(string $query): array
    {
        return $this->userRepo->search($query);
    }

    /**
     * Update personal data for a user
     *
     * @param int $userId User ID
     * @param array $data Personal data to update
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function updatePersonalData(int $userId, array $data): array
    {
        // Ensure DatosPersonales record exists
        $existing = $this->datosPersonalesRepo->findByUserId($userId);
        if (!$existing) {
            $this->datosPersonalesRepo->createForUser($userId);
        }

        if (!$this->datosPersonalesRepo->updateByUserId($userId, $data)) {
            return [
                'success' => false,
                'error' => 'Error actualizando datos personales.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Get user with personal data
     *
     * @param int $userId User ID
     * @return array|null User data with personal info
     */
    public function getWithPersonalData(int $userId): ?array
    {
        $user = $this->userRepo->getWithRelations($userId);
        if (!$user) {
            return null;
        }

        return $user;
    }

    /**
     * Get user with role
     *
     * @param int $userId User ID
     * @return array|null User data with role
     */
    public function getWithRole(int $userId): ?array
    {
        return $this->userRepo->getWithRelations($userId);
    }

    /**
     * Toggle user role (admin <-> user)
     *
     * @param int $userId User ID
     * @return array ['success' => bool, 'error' => string|null, 'new_role' => string|null]
     */
    public function toggleRole(int $userId): array
    {
        $userData = $this->userRepo->getWithRelations($userId);

        if (!$userData) {
            return [
                'success' => false,
                'error' => 'Usuario no encontrado.',
                'new_role' => null
            ];
        }

        $newRole = ($userData['role'] === 'admin') ? 'user' : 'admin';

        $result = $this->updateRole($userId, $newRole);

        return [
            'success' => $result['success'],
            'error' => $result['error'],
            'new_role' => $result['success'] ? $newRole : null
        ];
    }
}
