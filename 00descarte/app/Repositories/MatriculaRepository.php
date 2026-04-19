<?php
namespace App\Repositories;

/**
 * MatriculaRepository - Matricula data access layer
 *
 * Handles all database operations for matriculas table
 */
class MatriculaRepository extends BaseRepository
{
    protected string $table = 'matriculas';

    /**
     * Find matricula by user ID
     */
    public function findByUserId(int $userId): ?array
    {
        return $this->whereFirst('user_id', $userId);
    }

    /**
     * Find matricula by assigned number
     */
    public function findByNumero(int $numero): ?array
    {
        return $this->whereFirst('matriculaasignada', $numero);
    }

    /**
     * Update matricula by user ID
     */
    public function updateByUserId(int $userId, array $data): bool
    {
        $set = [];
        foreach ($data as $column => $value) {
            $set[] = "$column = ?";
        }
        $setClause = implode(', ', $set);

        $sql = "UPDATE {$this->table} SET $setClause WHERE user_id = ?";
        $values = array_values($data);
        $values[] = $userId;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Set freezedata (solicitud de revision)
     */
    public function setFreezedata(int $userId): bool
    {
        $fecha = date('Y-m-d');
        return $this->updateByUserId($userId, ['freezedata' => $fecha]);
    }

    /**
     * Assign matricula number
     */
    public function assignNumero(int $userId, int $numero): bool
    {
        $fecha = date('Y-m-d');
        return $this->updateByUserId($userId, [
            'matriculaasignada' => $numero,
            'aprobado' => $fecha
        ]);
    }

    /**
     * Grant matricula with commission
     */
    public function grant(int $userId, int $numero, int $comisionId): bool
    {
        $fecha = date('Y-m-d');
        return $this->updateByUserId($userId, [
            'matriculaasignada' => $numero,
            'aprobado' => $fecha,
            'comisionotorgante' => $comisionId
        ]);
    }

    /**
     * Process baja (discharge)
     */
    public function darDeBaja(int $userId, string $motivo): bool
    {
        $fecha = date('Y-m-d');
        return $this->updateByUserId($userId, [
            'baja' => $fecha,
            'motivobaja' => $motivo
        ]);
    }

    /**
     * Assign reviewer
     */
    public function assignRevisor(int $userId, int $revisorId): bool
    {
        $fecha = date('Y-m-d');
        return $this->updateByUserId($userId, [
            'revision' => $fecha,
            'revisor_id' => $revisorId
        ]);
    }

    /**
     * Assign verifier
     */
    public function assignVerificador(int $userId, int $verificadorId): bool
    {
        $fecha = date('Y-m-d');
        return $this->updateByUserId($userId, [
            'verificado' => $fecha,
            'verificador_id' => $verificadorId
        ]);
    }

    /**
     * Update estado (suspendeda/inhabilitada)
     */
    public function updateEstado(int $userId, ?string $estado): bool
    {
        return $this->updateByUserId($userId, ['estado' => $estado]);
    }

    /**
     * Get matricula status
     */
    public function getStatus(int $userId): string
    {
        $matricula = $this->findByUserId($userId);

        if (!$matricula) {
            return '';
        }

        return $this->determineStatus($matricula);
    }

    /**
     * Get detailed status information
     */
    public function getDetailedStatus(int $userId): array
    {
        $matricula = $this->findByUserId($userId);

        if (!$matricula) {
            return [
                'status' => '',
                'can_edit' => true,
                'can_request_revision' => true,
                'has_matricula' => false
            ];
        }

        $status = $this->determineStatus($matricula);

        return [
            'status' => $status,
            'can_edit' => $status === '',
            'can_request_revision' => $status === '',
            'has_matricula' => !empty($matricula['matriculaasignada']),
            'matricula' => $matricula
        ];
    }

    /**
     * Get next available matricula number
     */
    public function getNextNumero(): int
    {
        $stmt = $this->db->query("SELECT MAX(matriculaasignada) as max_num FROM {$this->table}");
        $result = $stmt->fetch();
        return ($result['max_num'] ?? 0) + 1;
    }

    /**
     * Get active matriculas
     */
    public function getActive(): array
    {
        $sql = "SELECT m.*, u.email, dp.nombre, dp.apellido
                FROM {$this->table} m
                JOIN users u ON m.user_id = u.id
                LEFT JOIN datospersonales dp ON m.user_id = dp.user_id
                WHERE m.matriculaasignada IS NOT NULL
                AND m.aprobado IS NOT NULL
                AND (m.baja IS NULL OR m.baja = '')
                ORDER BY m.matriculaasignada";

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Get matriculas by state
     */
    public function getByState(string $state): array
    {
        return $this->where('estado', $state);
    }

    /**
     * Determine status from matricula data
     */
    protected function determineStatus(array $matricula): string
    {
        if ($matricula['baja']) return 'Baja';
        if ($matricula['revision']) return 'Revisión';
        if ($matricula['verificado']) return 'Verificado';
        if ($matricula['aprobado']) {
            $status = 'Activa';
            if ($matricula['estado']) {
                $status .= ' - ' . $matricula['estado'];
            }
            return $status;
        }
        if ($matricula['freezedata']) return 'Solicitada';
        return '';
    }

    /**
     * Get user ID by matricula ID
     */
    public function getUserIdById(int $matriculaId): ?int
    {
        $stmt = $this->db->prepare("SELECT user_id FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $matriculaId]);
        $result = $stmt->fetch();
        return $result ? (int)$result['user_id'] : null;
    }
}
