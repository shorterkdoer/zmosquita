<?php

namespace App\Core;

use PDO;
use RuntimeException;
use DateTime;

abstract class Model
{
    // La subclase deberá definir la tabla.
    protected static string $table;
    // Clave primaria; por defecto 'id'
    protected static string $primaryKey = 'id';
    protected string $pendingquery = '';
    protected string $pendingcolumns = '';

    /**
     * Obtiene la conexión PDO.
     */
    protected static function getDB(): PDO
    {
        $config = include $_SESSION['directoriobase'] . '/config/db.php';
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

    protected static function getConnection(): PDO
    {
        return self::getDB();
    }
    /**
     * Busca un registro por su ID.
     */
    public static function find($id): ?array
    {
        $stmt = self::getDB()->prepare("SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ? $result : null;
    }

    /**
     * Trae todos los registros.
     */
    public static function all(): array
    {
        $stmt = self::getDB()->query("SELECT * FROM " . static::$table);
        return $stmt->fetchAll();
    }

    /**
     * Inserta un nuevo registro.
     * 
     * @param array $data Asociativo campo => valor
     */
    public static function create(array $data): bool
    {
        $columns = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $columnsList = implode(', ', $columns);
        $sql = "INSERT INTO " . static::$table . " ($columnsList) VALUES ($placeholders)";

        $stmt = self::getDB()->prepare($sql);
        //$stmt = self::getDB()->prepare("INSERT INTO " . static::$table . " ($columnsList) VALUES ($placeholders)");
        return $stmt->execute(array_values($data));
    }

    /**
     * Actualiza un registro.
     * 
     * @param mixed $id   Valor de la clave primaria.
     * @param array $data Asociativo campo => valor
     */
    public static function update($id, array $data): bool
    {
        $set = [];
        foreach ($data as $column => $value) {
            $set[] = "$column = ?";
        }
        $setClause = implode(', ', $set);

        $stmt = self::getDB()->prepare("UPDATE " . static::$table . " SET $setClause WHERE " . static::$primaryKey . " = ?");
        $values = array_values($data);
        $values[] = $id;

        return $stmt->execute($values);
    }

    /**
     * Elimina un registro.
     */
    public static function delete($id): bool
    {
        $stmt = self::getDB()->prepare("DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?");
        return $stmt->execute([$id]);
    }

    public static function getAll(array $options = [])
{
    $sql = "SELECT * FROM " . static::$table;
    // ... aplicar WHERE si hay filtros en $options
    $sql .= " LIMIT :limit OFFSET :offset";
    $stmt = self::getDB()->prepare($sql);
    $stmt->execute([
        ':limit'  => $options['limit']  ?? 100,
        ':offset' => $options['offset'] ?? 0,
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public static function countAll(array $filtros = [])
{
    $sql = "SELECT COUNT(*) FROM " . static::$table;
    // ... aplicar mismo WHERE
    return (int) self::getDB()->query($sql)->fetchColumn();
}

public static function findById(int $id): ?array
    {
        $table = static::$table ?? strtolower(static::class);

        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM `$table` WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ?: null;
    }


    /**
     * Devuelve pares id/label para poblar <select>.
     * @param array $opts
     *   - id_field      (string)  campo clave primaria. default: 'id'
     *   - mostrarcampo  (array|string) campos a concatenar en label. default: ['apellido','nombre']
     *   - separator     (string)  separador visible. default: ', '
     *   - where         (string)  WHERE opcional con placeholders (ej: "activo = :act")
     *   - params        (array)   bind params para WHERE (clave => valor)
     *   - order_by      (string)  'label' (default) o columna válida
     *   - limit         (int)     limita filas
     *   - distinct      (bool)    usa SELECT DISTINCT
     *   - collate       (string)  collation para ORDER BY label (ej: 'utf8mb4_spanish_ci')
     * @return array[]   [['id'=>'..','label'=>'..'], ...]
     */
    public static function HtmlDropDown(array $opts = []): array
    {
        // 1) Tabla
        $table = static::$table ?? self::classBaseToTable(static::class);

        // 2) Opciones
        $idField      = $opts['id_field']     ?? 'id';
        $mostrarcampo = $opts['mostrarcampo'] ?? ['apellido','nombre'];
        $sep          = $opts['separator']    ?? '';
        $where        = $opts['where']        ?? '';
        $params       = $opts['params']       ?? [];
        $orderBy      = $opts['order_by']     ?? 'label';
        $limit        = isset($opts['limit']) ? (int)$opts['limit'] : null;
        $distinct     = !empty($opts['distinct']);
        $collate      = $opts['collate']      ?? null;

        // 3) Normalizar campos
        if (is_string($mostrarcampo)) {
            $mostrarcampo = array_filter(array_map('trim', explode(',', $mostrarcampo)));
        }

        // 4) Validación mínima de identificadores
        $isValidIdent = static function(string $s): bool {
            return (bool)preg_match('/^[A-Za-z0-9_]+$/', $s);
        };
        if (!$isValidIdent($table) || !$isValidIdent($idField)) {
            throw new RuntimeException('Identificadores inválidos (tabla o id_field).');
        }
        foreach ($mostrarcampo as $c) {
            if (!$isValidIdent($c)) {
                throw new RuntimeException("Campo inválido en mostrarcampo: {$c}");
            }
        }

        // 5) Armar SQL seguro
        $idCol        = "`{$idField}`";
        $fieldListSql = implode(', ', array_map(fn($c) => "`{$c}`", $mostrarcampo));
        $labelExpr    = "TRIM(CONCAT_WS(:sep, {$fieldListSql}))";

        $distinctSql  = $distinct ? 'DISTINCT ' : '';
        $tableSql     = "`{$table}`";

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

//        echo $sql;
//        die();
        // 6) Ejecutar
        if(count($mostrarcampo)==1){$sep = '';}
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':sep', $sep, PDO::PARAM_STR);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . ltrim($k, ':'), $v);
        }
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($data as &$row) {
            $row['id']    = isset($row['id'])    ? (string)$row['id']    : '';
            $row['label'] = isset($row['label']) ? (string)$row['label'] : '';
        }
        unset($row);

        return $data;
    }

    /** Fallback: si no definen static::$table, convertir CamelCase -> snake_case minúscula */
    protected static function classBaseToTable(string $fqcn): string
    {
        $base = str_replace('\\', '/', $fqcn);
        $base = basename($base);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $base));
    }
public static function normalizeDate(string $input): ?string {
    $date = DateTime::createFromFormat('d/m/Y', $input);
    return $date ? $date->format('Y-m-d') : null;
}


    }