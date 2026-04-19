<?php
namespace App\Repositories;

use PDO;
use App\Core\Model;

/**
 * BaseRepository - Abstract base for all repositories
 *
 * Provides common CRUD operations and database access methods
 */
abstract class BaseRepository
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = $this->getConnection();
    }

    /**
     * Get database connection
     */
    protected function getConnection(): PDO
    {
        $configPath = $_SESSION['directoriobase'] ?? '/var/www/zmosquita';
        $config = require $configPath . '/config/db.php';

        return new PDO(
            $config['dsn'],
            $config['user'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]
        );
    }

    /**
     * Find a record by ID
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get all records
     */
    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    /**
     * Create a new record
     */
    public function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $this->db->prepare($sql)->execute(array_values($data));

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update a record
     */
    public function update(int $id, array $data): bool
    {
        $set = [];
        foreach ($data as $column => $value) {
            $set[] = "$column = ?";
        }
        $setClause = implode(', ', $set);

        $sql = "UPDATE {$this->table} SET $setClause WHERE {$this->primaryKey} = ?";
        $values = array_values($data);
        $values[] = $id;

        return $this->db->prepare($sql)->execute($values);
    }

    /**
     * Delete a record
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->prepare($sql)->execute([$id]);
    }

    /**
     * Get records where column equals value
     */
    public function where(string $column, $value): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE $column = ?"
        );
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }

    /**
     * Get first record where column equals value
     */
    public function whereFirst(string $column, $value): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE $column = ? LIMIT 1"
        );
        $stmt->execute([$value]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get paginated results
     */
    public function paginate(int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;

        $total = (int) $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();

        $sql = "SELECT * FROM {$this->table} LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        return [
            'data' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Execute custom query
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get count of records
     */
    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
    }

    /**
     * Check if record exists
     */
    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE {$this->primaryKey} = ?"
        );
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
