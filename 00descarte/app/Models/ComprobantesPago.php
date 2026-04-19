<?php

namespace App\Models;

use App\Core\Model;
use PDO;
class ComprobantesPago extends Model
{
    // Indica el nombre de la tabla
    protected static string $table = 'comprobantespago';

    /**
     * Crea un registro para la matrícula de un usuario.
     *
     * @param int $userId El id del usuario.
     * @return bool Resultado de la inserción.
     */
    public static function createForUser(int $userId, array $data): bool
    {
        // Solo seteamos user_id; los demás campos se dejarán en NULL (o valor por defecto)
        $data = [
            'user_id' => $userId
        ];
        return self::create($data);
    }

    public static function findById(int $id): ?array
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE id = ?");
        $stmt->execute([$id]);
        $comprob = $stmt->fetch();
        return $comprob ? $comprob : null;
    }

    public static function miscomprobantes(int $userid): ?array
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM comprobantespago WHERE user_id = ?");
        $stmt->execute([$userid]);
        $comprob_user = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $comprob_user ? $comprob_user : null;
    }

    public static function informopagos(int $userid): ?array
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM comprobantespago WHERE user_id = ?");
        $stmt->execute([$userid]);
        $comprob_user = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $comprob_user ? $comprob_user : null;
    }

    public static function meses(): ?array
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT DISTINCT DATE_FORMAT(fecha, '%m/%Y') AS mes_anio FROM `comprobantespago` ORDER BY mes_anio; " );
        $stmt->execute();
        $comprob_mes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $comprob_mes ? $comprob_mes : null;

    }
    public static function CustomQry(string $customquery): array

    {
        
        $stmt = self::getDB()->query($customquery);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

public static function createcobro(array $data): int
    {
        //$db = DB::getConnection();
        $db = self::getDB();
        $cols = array_keys($data);
        $fields = implode(', ', $cols);
        $placeholders = ':' . implode(', :', $cols);

        $sql = "INSERT INTO " . self::$table . " ($fields) VALUES ($placeholders)";
        $stmt = $db->prepare($sql);
        $stmt->execute($data);

        return (int)$db->lastInsertId();
    }

}
