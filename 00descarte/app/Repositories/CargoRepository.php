<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;

class CargoRepository extends BaseRepository
{
    protected string $table = 'cargos';
    protected string $primaryKey = 'id';

}
