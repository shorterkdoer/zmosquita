<?php

namespace App\Services;

use App\Repositories\DepartamentoRepository;

class DepartamentoService
{
    private DepartamentoRepository $repository;

    public function __construct()
    {
        $this->repository = new DepartamentoRepository();
    }

    public function getAll(): array
    {
        return $this->repository->all();
    }

    public function findById(int $id): ?array
    {
        return $this->repository->find($id);
    }

    public function create(array $data): int
    {
        // Validaciones
        $this->validateData($data);

        return $this->repository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        // Validaciones
        $this->validateData($data, $id);

        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    private function validateData(array $data, ?int $excludeId = null): void
    {
        if (empty($data['descripcion'])) {
            throw new \InvalidArgumentException('El campo descripcion es requerido');
        }
        if (empty($data['activo'])) {
            throw new \InvalidArgumentException('El campo activo es requerido');
        }
        if (empty($data['created_at'])) {
            throw new \InvalidArgumentException('El campo created_at es requerido');
        }
    }
}
