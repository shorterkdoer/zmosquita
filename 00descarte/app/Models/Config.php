<?php

namespace App\Models;

use App\Core\Model;

class Config extends Model
{
    // Nombre de la tabla en la base de datos
    protected static string $table = 'config';

    public static function findById(int $id): ?array
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE id = ?");
        $stmt->execute([$id]);
        $config = $stmt->fetch();
        return $config ? $config : null;
    }

}