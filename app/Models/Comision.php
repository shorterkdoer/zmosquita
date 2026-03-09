<?php

namespace App\Models;

use App\Core\Model;

class Comision extends Model
{
    // Nombre de la tabla en la base de datos
    protected static string $table = 'comision';

    public static function findById(int $id): ?array
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE id = ?");
        $stmt->execute([$id]);
        $comision = $stmt->fetch();
        return $comision ? $comision : null;
    }
    public static function espresidente(int $id): bool
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE activa <> 0 and user_presi = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
    public static function esvicepresidente(int $id): bool
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE activa <> 0 and user_vice = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;   
    }
    public static function activa(): ?array
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE activa = 1");
        $stmt->execute();
        $comision = $stmt->fetch();
        return $comision ? $comision : null;
    }
}