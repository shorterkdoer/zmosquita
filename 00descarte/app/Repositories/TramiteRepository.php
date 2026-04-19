<?php
namespace App\Repositories;

/**
 * TramiteRepository - Tramite (Paperwork) data access layer
 *
 * Handles all database operations for tramites table
 */
class TramiteRepository extends BaseRepository
{
    protected string $table = 'tramites';

    /**
     * Find tramites by user ID
     */
    public function findByUserId(int $userId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY fecha DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find all tramites by user ID
     */
    public function findAllByUserId(int $userId): array
    {
        return $this->where('user_id', $userId);
    }

    /**
     * Get pending tramites for review
     */
    public function getPendingRevision(): array
    {
        $sql = "SELECT t.*, u.email, dp.nombre, dp.apellido
                FROM {$this->table} t
                JOIN users u ON t.user_id = u.id
                LEFT JOIN datospersonales dp ON t.user_id = dp.user_id
                WHERE t.tipo = 'revision'
                AND t.revisor_id IS NULL
                ORDER BY t.fecha ASC";

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Get tramites by reviewer
     */
    public function getByRevisor(int $reviewerId): array
    {
        $sql = "SELECT t.*, u.email, dp.nombre, dp.apellido
                FROM {$this->table} t
                JOIN users u ON t.user_id = u.id
                LEFT JOIN datospersonales dp ON t.user_id = dp.user_id
                WHERE t.revisor_id = :reviewer_id
                ORDER BY t.fecha ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':reviewer_id' => $reviewerId]);
        return $stmt->fetchAll();
    }

    /**
     * Get tramites by verifier
     */
    public function getByVerificador(int $verifierId): array
    {
        $sql = "SELECT t.*, u.email, dp.nombre, dp.apellido
                FROM {$this->table} t
                JOIN users u ON t.user_id = u.id
                LEFT JOIN datospersonales dp ON t.user_id = dp.user_id
                WHERE t.verificador_id = :verifier_id
                ORDER BY t.fecha ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':verifier_id' => $verifierId]);
        return $stmt->fetchAll();
    }

    /**
     * Get tramites by state
     */
    public function getByEstado(string $estado): array
    {
        return $this->where('estado', $estado);
    }

    /**
     * Get tramites by type
     */
    public function getByTipo(string $tipo): array
    {
        return $this->where('tipo', $tipo);
    }

    /**
     * Assign reviewer to tramite
     */
    public function assignRevisor(int $tramiteId, int $reviewerId): bool
    {
        return $this->update($tramiteId, [
            'revisor_id' => $reviewerId,
            'fecha_asignacion' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Assign verifier to tramite
     */
    public function assignVerificador(int $tramiteId, int $verifierId): bool
    {
        return $this->update($tramiteId, [
            'verificador_id' => $verifierId,
            'fecha_verificacion' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Mark tramite as completed
     */
    public function markCompletado(int $tramiteId, string $observaciones = ''): bool
    {
        return $this->update($tramiteId, [
            'estado' => 'revisado',
            'fecha_completado' => date('Y-m-d H:i:s'),
            'observaciones' => $observaciones
        ]);
    }

    /**
     * Mark tramite as rejected
     */
    public function markRechazado(int $tramiteId, string $observaciones = ''): bool
    {
        return $this->update($tramiteId, [
            'estado' => 'rechazado',
            'fecha_completado' => date('Y-m-d H:i:s'),
            'observaciones' => $observaciones
        ]);
    }

    /**
     * Get tramite status for user
     */
    public function getStatus(int $userId): array
    {
        $tramite = $this->findByUserId($userId);

        if (!$tramite) {
            return [
                'has_tramite' => false,
                'estado' => null,
                'mensaje' => 'No tiene trámites en curso'
            ];
        }

        return [
            'has_tramite' => true,
            'estado' => $tramite['estado'] ?? 'pendiente',
            'fecha' => $tramite['fecha'],
            'revisor_id' => $tramite['revisor_id'] ?? null,
            'verificador_id' => $tramite['verificador_id'] ?? null,
            'observaciones' => $tramite['observaciones'] ?? '',
        ];
    }

    /**
     * Get tramites by date range
     */
    public function getByDateRange(string $fechaFrom, string $fechaTo): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE fecha BETWEEN :from AND :to
                ORDER BY fecha DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':from' => $fechaFrom, ':to' => $fechaTo]);
        return $stmt->fetchAll();
    }

    /**
     * Get tramite with user data
     */
    public function getWithUserData(int $tramiteId): ?array
    {
        $sql = "SELECT t.*, u.email, dp.nombre, dp.apellido
                FROM {$this->table} t
                JOIN users u ON t.user_id = u.id
                LEFT JOIN datospersonales dp ON t.user_id = dp.user_id
                WHERE t.id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $tramiteId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
