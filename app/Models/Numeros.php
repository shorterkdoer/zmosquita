<?php

namespace App\Models;

use App\Core\Model;

class Numeros extends Model
{
    // Nombre de la tabla en la base de datos
    protected static string $table = 'numeros';

    public static function findByRotulo(string $clave): ?array
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE rotulo = ?");
        $stmt->execute([$clave]);
        $clavevalor = $stmt->fetch();
        return $clavevalor ? $clavevalor : null;
    }


    public static function updatebyRotulo( int $value, string $whereValue): bool
    {
        $db = self::getDB();
        $xtable = static::$table;

        $sql = "UPDATE `$xtable` SET `valor` = :value WHERE `rotulo` = :whereValue";
        $stmt = $db->prepare($sql);

        return $stmt->execute([
            ':value' => $value,
            ':whereValue' => $whereValue
        ]);
    }

}
