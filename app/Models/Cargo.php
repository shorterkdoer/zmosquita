<?php


namespace App\Models;

use App\Core\Model;

class Cargo extends Model {
    protected static $table = 'cargos';
/*
    public static function all() {
        return self::query("SELECT * FROM cargos ORDER BY jerarquia ASC");
    }

    public static function find($id) {
        return self::queryOne("SELECT * FROM cargos WHERE id = ?", [$id]);
    }
*/
    public static function getNombre($id) {
        $cargo = self::find($id);
        return $cargo ? $cargo['nombre'] : null;
    }
}
