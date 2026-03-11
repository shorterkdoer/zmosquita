<?php
namespace App\Repositories;

/**
 * CitaRepository - Cita (Appointment) data access layer
 *
 * Handles all database operations for agendadecitas table
 */
class CitaRepository extends BaseRepository
{
    protected string $table = 'agendadecitas';

    /**
     * Find citas by user ID
     */
    public function findByUserId(int $userId): array
    {
        return $this->where('user_id', $userId);
    }

    /**
     * Find citas by date
     */
    public function findByDate(string $fecha): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE fecha = :fecha ORDER BY hora";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':fecha' => $fecha]);
        return $stmt->fetchAll();
    }

    /**
     * Find citas by date range
     */
    public function findByDateRange(string $fechaFrom, string $fechaTo): array
    {
        $sql = "SELECT ac.*, dp.nombre, dp.apellido, u.email
                FROM {$this->table} ac
                JOIN users u ON ac.user_id = u.id
                LEFT JOIN datospersonales dp ON ac.user_id = dp.user_id
                WHERE ac.fecha BETWEEN :from AND :to
                ORDER BY ac.fecha, ac.hora";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':from' => $fechaFrom, ':to' => $fechaTo]);
        return $stmt->fetchAll();
    }

    /**
     * Get booked slots for a specific date
     */
    public function getBookedSlots(string $fecha, array $estados = ['confirmada']): array
    {
        $placeholders = implode(',', array_fill(0, count($estados), '?'));
        $sql = "SELECT hora FROM {$this->table} WHERE fecha = ? AND estado IN ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $params = array_merge([$fecha], $estados);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Check if slot is available
     */
    public function isSlotAvailable(string $fecha, string $hora, array $estados = ['confirmada', 'pendiente']): bool
    {
        $placeholders = implode(',', array_fill(0, count($estados), '?'));
        $sql = "SELECT COUNT(*) as count FROM {$this->table}
                WHERE fecha = ? AND hora = ? AND estado IN ($placeholders)";

        $stmt = $this->db->prepare($sql);
        $params = array_merge([$fecha, $hora], $estados);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return ($result['count'] ?? 0) == 0;
    }

    /**
     * Get upcoming citas for a user
     */
    public function getUpcomingByUserId(int $userId): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE user_id = :user_id
                AND CONCAT(fecha, ' ', hora) >= NOW()
                AND estado NOT IN ('cancelada', 'completada')
                ORDER BY fecha, hora";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Update estado
     */
    public function updateEstado(int $citaId, string $estado): bool
    {
        return $this->update($citaId, ['estado' => $estado]);
    }

    /**
     * Confirm cita
     */
    public function confirmar(int $citaId): bool
    {
        return $this->updateEstado($citaId, 'confirmada');
    }

    /**
     * Cancel cita
     */
    public function cancelar(int $citaId): bool
    {
        return $this->updateEstado($citaId, 'cancelada');
    }

    /**
     * Complete cita
     */
    public function completar(int $citaId, string $observaciones = ''): bool
    {
        return $this->update($citaId, [
            'estado' => 'completada',
            'observaciones' => $observaciones
        ]);
    }

    /**
     * Get citas by estado
     */
    public function getByEstado(string $estado): array
    {
        return $this->where('estado', $estado);
    }

    /**
     * Get citas for today
     */
    public function getToday(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE fecha = CURDATE() ORDER BY hora";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Get citas with user data
     */
    public function getWithUserData(int $citaId): ?array
    {
        $sql = "SELECT ac.*, u.email, dp.nombre, dp.apellido
                FROM {$this->table} ac
                JOIN users u ON ac.user_id = u.id
                LEFT JOIN datospersonales dp ON ac.user_id = dp.user_id
                WHERE ac.id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $citaId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
