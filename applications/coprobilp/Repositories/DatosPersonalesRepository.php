<?php
namespace App\Repositories;

/**
 * DatosPersonalesRepository - Datos Personales data access layer
 *
 * Handles all database operations for datospersonales table
 */
class DatosPersonalesRepository extends BaseRepository
{
    protected string $table = 'datospersonales';

    /**
     * Find datos personales by user ID
     */
    public function findByUserId(int $userId): ?array
    {
        return $this->whereFirst('user_id', $userId);
    }

    /**
     * Update datos personales by user ID
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
     * Create datos personales for user
     */
    public function createForUser(int $userId): bool
    {
        $sql = "INSERT INTO {$this->table} (user_id) VALUES (:user_id)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':user_id' => $userId]);
    }

    /**
     * Get full name for user
     */
    public function getFullName(int $userId): string
    {
        $datos = $this->findByUserId($userId);

        if (!$datos) {
            return '';
        }

        return trim(($datos['apellido'] ?? '') . ' ' . ($datos['nombre'] ?? ''));
    }

    /**
     * Search by name or DNI
     */
    public function search(string $query): array
    {
        $sql = "SELECT dp.*, u.email
                FROM {$this->table} dp
                JOIN users u ON dp.user_id = u.id
                WHERE dp.dni LIKE :query
                   OR CONCAT(dp.apellido, ' ', dp.nombre) LIKE :query
                   OR CONCAT(dp.nombre, ' ', dp.apellido) LIKE :query
                ORDER BY dp.apellido, dp.nombre
                LIMIT 100";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':query' => "%$query%"]);
        return $stmt->fetchAll();
    }

    /**
     * Get dropdown options for selects
     */
    public function getDropdownOptions(array $filters = []): array
    {
        $sql = "SELECT dp.user_id as id,
                       CONCAT(COALESCE(dp.apellido, ''), ', ', COALESCE(dp.nombre, '')) as label
                FROM {$this->table} dp
                JOIN users u ON dp.user_id = u.id";

        $params = [];

        if (!empty($filters['has_matricula'])) {
            $sql .= " JOIN matriculas m ON dp.user_id = m.user_id";
            if ($filters['has_matricula']) {
                $sql .= " WHERE m.matriculaasignada IS NOT NULL";
            } else {
                $sql .= " WHERE m.matriculaasignada IS NULL";
            }
        }

        $sql .= " ORDER BY dp.apellido, dp.nombre";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get users by location (provincia/ciudad)
     */
    public function getByLocation(int $provinciaId, ?int $ciudadId = null): array
    {
        $sql = "SELECT dp.*, u.email
                FROM {$this->table} dp
                JOIN users u ON dp.user_id = u.id
                WHERE dp.provincia_id = :provincia_id";

        $params = [':provincia_id' => $provinciaId];

        if ($ciudadId !== null) {
            $sql .= " AND dp.ciudad_id = :ciudad_id";
            $params[':ciudad_id'] = $ciudadId;
        }

        $sql .= " ORDER BY dp.apellido, dp.nombre";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
