<?php

declare(strict_types=1);

namespace {{ namespace }};

use {{ repository_namespace }}\{{ repository_class }};

final class {{ service_class }}
{
    public function __construct(
        private {{ repository_class }} ${{ repository_property }}
    ) {
    }

    public function getAll(): array
    {
        return $this->{{ repository_property }}->findAll();
    }

    public function findById(int $id): ?array
    {
        return $this->{{ repository_property }}->find($id);
    }

    public function create(array $data): array
    {
        return $this->{{ repository_property }}->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->{{ repository_property }}->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->{{ repository_property }}->delete($id);
    }
}
