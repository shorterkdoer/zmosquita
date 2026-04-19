<?php

namespace App\Models;

use App\Core\Model;

class DocumentTemplate extends Model
{
    // Nombre de la tabla en la base de datos
    protected static string $table = 'document_templates';
/*
    public static function findById(int $id): ?array
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE id = ?");
        $stmt->execute([$id]);
        $ciudad = $stmt->fetch();
        return $ciudad ? $ciudad : null;
    }
*/
}
