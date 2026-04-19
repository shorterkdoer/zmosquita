<?php

namespace App\Services;

use App\Repositories\CargoRepository;

class CargoService
{
    private CargoRepository $repository;

    public function __construct()
    {
        $this->repository = new CargoRepository();
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
        if (empty($data['jerarquia'])) {
            throw new \InvalidArgumentException('El campo jerarquia es requerido');
        }
    }
}
