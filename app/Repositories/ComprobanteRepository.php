<?php
namespace App\Repositories;

/**
 * ComprobanteRepository - Payment receipts data access layer
 *
 * Handles all database operations for comprobantespago table
 */
class ComprobanteRepository extends BaseRepository
{
    protected string $table = 'comprobantespago';

    /**
     * Find comprobantes by user ID
     */
    public function findByUserId(int $userId): array
    {
        return $this->where('user_id', $userId);
    }

    /**
     * Find comprobantes by month and year
     */
    public function findByPeriod(string $mes, string $anio): array
    {
        $sql = "SELECT cp.*, u.email, dp.nombre, dp.apellido
                FROM {$this->table} cp
                JOIN users u ON cp.user_id = u.id
                LEFT JOIN datospersonales dp ON cp.user_id = dp.user_id
                WHERE cp.mes = :mes AND cp.anio = :anio
                ORDER BY cp.fecha DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':mes' => $mes, ':anio' => $anio]);
        return $stmt->fetchAll();
    }

    /**
     * Get pending comprobantes (not verified)
     */
    public function getPending(): array
    {
        $sql = "SELECT cp.*, u.email, dp.nombre, dp.apellido
                FROM {$this->table} cp
                JOIN users u ON cp.user_id = u.id
                LEFT JOIN datospersonales dp ON cp.user_id = dp.user_id
                WHERE cp.verificado IS NULL OR cp.verificado = 0
                ORDER BY cp.fecha DESC";

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Mark comprobante as verified
     */
    public function markVerified(int $id): bool
    {
        return $this->update($id, [
            'verificado' => 1,
            'fecha_verificacion' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get summary by period
     */
    public function getSummaryByPeriod(string $anio): array
    {
        $sql = "SELECT mes, COUNT(*) as cantidad
                FROM {$this->table}
                WHERE anio = :anio
                GROUP BY mes
                ORDER BY FIELD(mes, 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':anio' => $anio]);
        return $stmt->fetchAll();
    }

    /**
     * Get latest comprobante for user
     */
    public function getLatestForUser(int $userId): ?array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE user_id = :user_id
                ORDER BY fecha DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Check if user has payment for period
     */
    public function hasPaymentForPeriod(int $userId, string $mes, string $anio): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}
                WHERE user_id = :user_id AND mes = :mes AND anio = :anio";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId, ':mes' => $mes, ':anio' => $anio]);
        $result = $stmt->fetch();

        return ($result['count'] ?? 0) > 0;
    }
}
