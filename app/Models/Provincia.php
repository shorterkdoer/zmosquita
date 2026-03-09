<?php

namespace App\Models;

use App\Core\Model;

class Provincia extends Model
{
    // Nombre de la tabla en la base de datos
    protected static string $table = 'provincias';

    public static function findById(int $id): ?array
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE id = ?");
        $stmt->execute([$id]);
        $provincias = $stmt->fetch();
        return $provincias ? $provincias : null;
    }
}
