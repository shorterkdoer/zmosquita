<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;

class DepartamentoRepository extends BaseRepository
{
    protected string $table = 'departamentos';
    protected string $primaryKey = 'id';

    public function findByCargo(int $id): array
    {
        return $this->where('cargo_id', $id);
    }

}
