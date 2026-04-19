<?php
namespace App\Repositories;

/**
 * UserRepository - User data access layer
 *
 * Handles all database operations for users table
 */
class UserRepository extends BaseRepository
{
    protected string $table = 'users';

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->whereFirst('email', $email);
    }

    /**
     * Find user by activation token
     */
    public function findByActivationToken(string $token): ?array
    {
        return $this->whereFirst('activation_token', $token);
    }

    /**
     * Find user by password reset token
     */
    public function findByResetToken(string $token): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE password_reset_token = ?
             AND password_reset_expires_at >= NOW()
             LIMIT 1"
        );
        $stmt->execute([$token]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Activate user account
     */
    public function activate(int $userId): bool
    {
        return $this->update($userId, [
            'active' => 1,
            'activation_token' => null,
            'mailed' => date('Y/m/d')
        ]);
    }

    /**
     * Set password reset token
     */
    public function setPasswordResetToken(string $email, string $token, string $expires): bool
    {
        $sql = "UPDATE {$this->table}
                SET password_reset_token = :token,
                    password_reset_expires_at = :expires
                WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':token' => $token,
            ':expires' => $expires,
            ':email' => $email,
        ]);
    }

    /**
     * Clear password reset token
     */
    public function clearPasswordResetToken(int $userId): bool
    {
        $sql = "UPDATE {$this->table}
                SET password_reset_token = NULL,
                    password_reset_expires_at = NULL
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $userId]);
    }

    /**
     * Update user password
     */
    public function updatePassword(int $userId, string $newHash): bool
    {
        $sql = "UPDATE {$this->table}
                SET password = :pw
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':pw' => $newHash,
            ':id' => $userId,
        ]);
    }

    /**
     * Update user role
     */
    public function updateRole(int $userId, string $role): bool
    {
        return $this->update($userId, ['role' => $role]);
    }

    /**
     * Get pending users (not activated)
     */
    public function getPending(): array
    {
        $sql = "SELECT id, email, created_at FROM {$this->table}
                WHERE active = 0 OR activation_token IS NOT NULL
                ORDER BY created_at DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Get users by role
     */
    public function getByRole(string $role): array
    {
        return $this->where('role', $role);
    }

    /**
     * Search users by email or name (via datos personales)
     */
    public function search(string $query): array
    {
        $sql = "SELECT u.id, u.email, u.role,
                       CONCAT(COALESCE(dp.apellido, ''), ' ', COALESCE(dp.nombre, '')) as nombre_completo
                FROM {$this->table} u
                LEFT JOIN datospersonales dp ON u.id = dp.user_id
                WHERE u.email LIKE :query
                   OR CONCAT(COALESCE(dp.apellido, ''), ' ', COALESCE(dp.nombre, '')) LIKE :query
                ORDER BY u.email
                LIMIT 50";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':query' => "%$query%"]);
        return $stmt->fetchAll();
    }

    /**
     * Get user with related data (matricula, datos personales)
     */
    public function getWithRelations(int $userId): ?array
    {
        $sql = "SELECT u.*,
                       m.matriculaasignada, m.aprobado, m.baja, m.estado,
                       dp.nombre, dp.apellido, dp.dni
                FROM {$this->table} u
                LEFT JOIN matriculas m ON u.id = m.user_id
                LEFT JOIN datospersonales dp ON u.id = dp.user_id
                WHERE u.id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
