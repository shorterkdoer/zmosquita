<?php

namespace App\Models;
use PDO;

use App\Core\Model;

class AgendaDeCitas extends Model
{
    // Nombre de la tabla en la base de datos
    protected static string $table = 'agendadecitas';

    public static function findById(int $id): ?array
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE id = ?");
        $stmt->execute([$id]);
        $provincias = $stmt->fetch();
        return $provincias ? $provincias : null;
    }


    public static function CustomQry(string $customquery): array

    {
        $stmt = self::getDB()->query($customquery);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

        public static function CustomError(): string

    {
        $localerror = self::getDB()->errorInfo();
        return $localerror[2] ?? 'Error desconocido en la consulta SQL';

    }


}
