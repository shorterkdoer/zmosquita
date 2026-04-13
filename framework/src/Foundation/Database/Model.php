<?php

namespace Foundation\Database;

use PDO;
use RuntimeException;
use DateTime;

/**
 * Base Model class for the framework
 *
 * Provides CRUD operations and common database functionality.
 * Extend this class to create application-specific models.
 */
abstract class Model
{
    /**
     * The table associated with the model.
     * Subclasses must define this property.
     */
    protected static string $table;

    /**
     * The primary key for the model.
     * Default is 'id'.
     */
    protected static string $primaryKey = 'id';

    /**
     * Get the PDO database connection.
     *
     * @return PDO
     */
    protected static function getDB(): PDO
    {
        $config = require $_SESSION['directoriobase'] . '/config/db.php';
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
     * Get the PDO connection (alias for getDB).
     *
     * @return PDO
     */
    protected static function getConnection(): PDO
    {
        return self::getDB();
    }

    /**
     * Find a record by its primary key.
     *
     * @param mixed $id The primary key value
     * @return array|null The record as an associative array, or null if not found
     */
    public static function find($id): ?array
    {
        $stmt = self::getDB()->prepare(
            "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ? $result : null;
    }

    /**
     * Find a record by ID.
     *
     * @param int $id
     * @return array|null
     */
    public static function findById(int $id): ?array
    {
        $table = static::$table ?? self::classBaseToTable(static::class);

        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM `$table` WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ?: null;
    }

    /**
     * Get all records from the table.
     *
     * @return array All records as associative arrays
     */
    public static function all(): array
    {
        $stmt = self::getDB()->query("SELECT * FROM " . static::$table);
        return $stmt->fetchAll();
    }

    /**
     * Get records with optional filters, limit and offset.
     *
     * @param array $options Options like 'limit', 'offset', 'where'
     * @return array
     */
    public static function getAll(array $options = []): array
    {
        $sql = "SELECT * FROM " . static::$table;
        // TODO: Apply WHERE filters if provided in $options
        $sql .= " LIMIT :limit OFFSET :offset";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute([
            ':limit' => $options['limit'] ?? 100,
            ':offset' => $options['offset'] ?? 0,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count all records.
     *
     * @param array $filters Optional filters
     * @return int
     */
    public static function countAll(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) FROM " . static::$table;
        // TODO: Apply same WHERE filters as getAll()
        return (int) self::getDB()->query($sql)->fetchColumn();
    }

    /**
     * Insert a new record.
     *
     * @param array $data Associative array of field => value pairs
     * @return bool True on success, false on failure
     */
    public static function create(array $data): bool
    {
        $columns = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $columnsList = implode(', ', $columns);
        $sql = "INSERT INTO " . static::$table . " ($columnsList) VALUES ($placeholders)";

        $stmt = self::getDB()->prepare($sql);
        return $stmt->execute(array_values($data));
    }

    /**
     * Update a record.
     *
     * @param mixed $id The primary key value
     * @param array $data Associative array of field => value pairs
     * @return bool True on success, false on failure
     */
    public static function update($id, array $data): bool
    {
        $set = [];
        foreach ($data as $column => $value) {
            $set[] = "$column = ?";
        }
        $setClause = implode(', ', $set);

        $stmt = self::getDB()->prepare(
            "UPDATE " . static::$table . " SET $setClause WHERE " . static::$primaryKey . " = ?"
        );
        $values = array_values($data);
        $values[] = $id;

        return $stmt->execute($values);
    }

    /**
     * Delete a record.
     *
     * @param mixed $id The primary key value
     * @return bool True on success, false on failure
     */
    public static function delete($id): bool
    {
        $stmt = self::getDB()->prepare(
            "DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?"
        );
        return $stmt->execute([$id]);
    }

    /**
     * Execute a custom query and return results.
     *
     * @param string $query The SQL query
     * @return array Results as associative arrays
     */
    public static function customQuery(string $query): array
    {
        $stmt = self::getDB()->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the last database error message.
     *
     * @return string Error message
     */
    public static function getLastError(): string
    {
        $error = self::getDB()->errorInfo();
        return $error[2] ?? 'Unknown database error';
    }

    /**
     * Get id/label pairs for HTML select dropdowns.
     *
     * @param array $opts Options:
     *   - id_field: Primary key field (default: 'id')
     *   - mostrarcampo: Field(s) to display in label (default: ['apellido', 'nombre'])
     *   - separator: Separator between multiple fields (default: ', ')
     *   - where: WHERE clause with placeholders
     *   - params: Bind parameters for WHERE clause
     *   - order_by: Order field (default: 'label')
     *   - limit: Limit results
     *   - distinct: Use DISTINCT
     *   - collate: Collation for ORDER BY
     * @return array Array of ['id' => '..', 'label' => '..'] pairs
     */
    public static function HtmlDropDown(array $opts = []): array
    {
        // Table name
        $table = static::$table ?? self::classBaseToTable(static::class);

        // Options
        $idField = $opts['id_field'] ?? 'id';
        $mostrarcampo = $opts['mostrarcampo'] ?? ['apellido', 'nombre'];
        $sep = $opts['separator'] ?? ', ';
        $where = $opts['where'] ?? '';
        $params = $opts['params'] ?? [];
        $orderBy = $opts['order_by'] ?? 'label';
        $limit = isset($opts['limit']) ? (int) $opts['limit'] : null;
        $distinct = !empty($opts['distinct']);
        $collate = $opts['collate'] ?? null;

        // Normalize display fields
        if (is_string($mostrarcampo)) {
            $mostrarcampo = array_filter(array_map('trim', explode(',', $mostrarcampo)));
        }

        // Validate identifiers
        $isValidIdent = static function (string $s): bool {
            return (bool) preg_match('/^[A-Za-z0-9_]+$/', $s);
        };

        if (!$isValidIdent($table) || !$isValidIdent($idField)) {
            throw new RuntimeException('Invalid identifiers (table or id_field).');
        }

        foreach ($mostrarcampo as $c) {
            if (!$isValidIdent($c)) {
                throw new RuntimeException("Invalid field in mostrarcampo: {$c}");
            }
        }

        // Build SQL
        $idCol = "`{$idField}`";
        $fieldListSql = implode(', ', array_map(fn ($c) => "`{$c}`", $mostrarcampo));
        $labelExpr = "TRIM(CONCAT_WS(:sep, {$fieldListSql}))";

        $distinctSql = $distinct ? 'DISTINCT ' : '';
        $tableSql = "`{$table}`";

        $sql = "SELECT {$distinctSql}{$idCol} AS id, {$labelExpr} AS label FROM {$tableSql}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }

        if ($orderBy === 'label') {
            $sql .= " ORDER BY label" . ($collate ? " COLLATE {$collate}" : '');
        } else {
            $sql .= $isValidIdent($orderBy) ? " ORDER BY `{$orderBy}`" : " ORDER BY label";
        }

        if ($limit !== null && $limit > 0) {
            $sql .= " LIMIT {$limit}";
        }

        // Execute
        if (count($mostrarcampo) == 1) {
            $sep = '';
        }

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':sep', $sep, PDO::PARAM_STR);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . ltrim($k, ':'), $v);
        }
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($data as &$row) {
            $row['id'] = isset($row['id']) ? (string) $row['id'] : '';
            $row['label'] = isset($row['label']) ? (string) $row['label'] : '';
        }
        unset($row);

        return $data;
    }

    /**
     * Normalize date from d/m/Y to Y-m-d format.
     *
     * @param string $input Date in d/m/Y format
     * @return string|null Date in Y-m-d format or null if invalid
     */
    public static function normalizeDate(string $input): ?string
    {
        $date = DateTime::createFromFormat('d/m/Y', $input);
        return $date ? $date->format('Y-m-d') : null;
    }

    /**
     * Fallback: Convert class name to table name (CamelCase to snake_case).
     *
     * @param string $fqcn Fully qualified class name
     * @return string Table name
     */
    protected static function classBaseToTable(string $fqcn): string
    {
        $base = str_replace('\\', '/', $fqcn);
        $base = basename($base);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $base));
    }
}
