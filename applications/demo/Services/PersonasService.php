<?php

declare(strict_types=1);

namespace Applications\Demo\Services;

use Applications\Demo\Repositories\PersonasRepository;

final class PersonasService
{
    public function __construct(
        private PersonasRepository $personasRepository
    ) {
    }

    public function getAll(): array
    {
        return $this->personasRepository->findAll();
    }

    public function findById(int $id): ?array
    {
        return $this->personasRepository->find($id);
    }

    public function create(array $data): array
    {
        return $this->personasRepository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->personasRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->personasRepository->delete($id);
    }
}
