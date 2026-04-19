<?php

namespace App\Models;

use App\Core\Model;

class Departamento extends Model
{
    protected static string $table = 'departamentos';
    protected static string $primaryKey = 'id';

    public function getCargo(): ?array
    {
        if (empty($this['cargo_id'])) {
            return null;
        }
        return Cargo::find($this['cargo_id']);
    }

}
