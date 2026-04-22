<?php

declare(strict_types=1);

namespace {{ namespace }};

use ZMosquita\Core\Database\QueryBuilder;
use ZMosquita\Core\Database\TableResolver;
use ZMosquita\Core\Support\Container;

final class {{ repository_class }}
{
    private QueryBuilder $db;
    private TableResolver $tables;

    public function __construct()
    {
        $this->db = Container::instance()->get(QueryBuilder::class);
        $this->tables = Container::instance()->get(TableResolver::class);
    }

    private function table(): string
    {
        return $this->tables->app('{{ resource }}', '{{ resource }}');
    }

    public function find(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table()} WHERE {{ primary_key }} = ?",
            [$id]
        );
    }

    public function findAll(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table()} ORDER BY {{ primary_key }} DESC"
        );
    }

    public function create(array $data): array
    {
        $this->db->insert($this->table(), $data);
        $id = (int) $this->db->lastInsertId();

        return $this->find($id);
    }

    public function update(int $id, array $data): bool
    {
        $set = [];
        foreach ($data as $column => $value) {
            $set[] = "{$column} = ?";
        }

        $sql = "UPDATE {$this->table()} SET " . implode(', ', $set) . " WHERE {{ primary_key }} = ?";
        $params = array_values($data);
        $params[] = $id;

        return $this->db->execute($sql, $params);
    }

    public function delete(int $id): bool
    {
        return $this->db->execute(
            "DELETE FROM {$this->table()} WHERE {{ primary_key }} = ?",
            [$id]
        );
    }
}
