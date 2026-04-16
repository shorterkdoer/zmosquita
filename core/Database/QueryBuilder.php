<?php

declare(strict_types=1);

namespace ZMosquita\Core\Database;

use RuntimeException;

final class QueryBuilder
{
    private string $table = '';

    /** @var array<int, string> */
    private array $columns = ['*'];

    /** @var array<int, array{type:string,sql:string,bindings:array<int|string,mixed>}> */
    private array $wheres = [];

    /** @var array<int, string> */
    private array $joins = [];

    /** @var array<int, string> */
    private array $orders = [];

    private ?int $limitValue = null;
    private ?int $offsetValue = null;

    public function __construct(
        private Connection $db
    ) {
    }

    public function table(string $table): static
    {
        $clone = clone $this;
        $clone->table = $table;
        return $clone;
    }

    public function select(array|string $columns = ['*']): static
    {
        $clone = clone $this;
        $clone->columns = is_array($columns) ? $columns : [$columns];
        return $clone;
    }

    public function where(array|string $column, mixed $operator = null, mixed $value = null): static
    {
        $clone = clone $this;

        if (is_array($column)) {
            foreach ($column as $key => $val) {
                $clone = $clone->where($key, '=', $val);
            }
            return $clone;
        }

        [$operator, $value] = $this->normalizeOperatorAndValue($operator, $value);

        $placeholder = $this->makePlaceholder($column, count($clone->wheres));
        $clone->wheres[] = [
            'type' => 'AND',
            'sql' => "{$column} {$operator} {$placeholder}",
            'bindings' => [$placeholder => $value],
        ];

        return $clone;
    }

    public function orWhere(array|string $column, mixed $operator = null, mixed $value = null): static
    {
        $clone = clone $this;

        if (is_array($column)) {
            foreach ($column as $key => $val) {
                $clone = $clone->orWhere($key, '=', $val);
            }
            return $clone;
        }

        [$operator, $value] = $this->normalizeOperatorAndValue($operator, $value);

        $placeholder = $this->makePlaceholder($column, count($clone->wheres));
        $clone->wheres[] = [
            'type' => 'OR',
            'sql' => "{$column} {$operator} {$placeholder}",
            'bindings' => [$placeholder => $value],
        ];

        return $clone;
    }

    public function whereRaw(string $sql, array $bindings = []): static
    {
        $clone = clone $this;
        $clone->wheres[] = [
            'type' => 'AND',
            'sql' => $sql,
            'bindings' => $bindings,
        ];
        return $clone;
    }

    public function whereNull(string $column): static
    {
        $clone = clone $this;
        $clone->wheres[] = [
            'type' => 'AND',
            'sql' => "{$column} IS NULL",
            'bindings' => [],
        ];
        return $clone;
    }

    public function whereNotNull(string $column): static
    {
        $clone = clone $this;
        $clone->wheres[] = [
            'type' => 'AND',
            'sql' => "{$column} IS NOT NULL",
            'bindings' => [],
        ];
        return $clone;
    }

    public function whereIn(string $column, array $values): static
    {
        $clone = clone $this;

        if ($values === []) {
            $clone->wheres[] = [
                'type' => 'AND',
                'sql' => '1 = 0',
                'bindings' => [],
            ];
            return $clone;
        }

        $placeholders = [];
        $bindings = [];

        foreach (array_values($values) as $i => $value) {
            $ph = $this->makePlaceholder($column . '_in', count($clone->wheres) . $i);
            $placeholders[] = $ph;
            $bindings[$ph] = $value;
        }

        $clone->wheres[] = [
            'type' => 'AND',
            'sql' => "{$column} IN (" . implode(', ', $placeholders) . ")",
            'bindings' => $bindings,
        ];

        return $clone;
    }

    public function join(string $table, string $first, string $operator, string $second): static
    {
        $clone = clone $this;
        $clone->joins[] = "INNER JOIN {$table} ON {$first} {$operator} {$second}";
        return $clone;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): static
    {
        $clone = clone $this;
        $clone->joins[] = "LEFT JOIN {$table} ON {$first} {$operator} {$second}";
        return $clone;
    }

    public function orderBy(string $column, string $direction = 'asc'): static
    {
        $clone = clone $this;
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $clone->orders[] = "{$column} {$direction}";
        return $clone;
    }

    public function limit(int $limit): static
    {
        $clone = clone $this;
        $clone->limitValue = max(0, $limit);
        return $clone;
    }

    public function offset(int $offset): static
    {
        $clone = clone $this;
        $clone->offsetValue = max(0, $offset);
        return $clone;
    }

    public function first(): ?array
    {
        $clone = clone $this;
        if ($clone->limitValue === null) {
            $clone->limitValue = 1;
        }

        return $this->db->fetchOne($clone->toSql(), $clone->getBindings());
    }

    public function get(): array
    {
        return $this->db->fetchAll($this->toSql(), $this->getBindings());
    }

    public function count(string $column = '*'): int
    {
        $clone = clone $this;
        $clone->columns = ["COUNT({$column}) AS aggregate"];
        $clone->orders = [];
        $clone->limitValue = null;
        $clone->offsetValue = null;

        $row = $this->db->fetchOne($clone->toSql(), $clone->getBindings());

        return (int)($row['aggregate'] ?? 0);
    }

    public function exists(): bool
    {
    	return $this->count() > 0;
    }
    /**
     * @return array{
     *   data: array<int, array<string, mixed>>,
     *   total: int,
     *   per_page: int,
     *   current_page: int,
     *   last_page: int
     * }
     */
    public function paginate(int $perPage = 15, int $page = 1): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);

        $total = $this->count();
        $lastPage = max(1, (int)ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $data = $this
            ->limit($perPage)
            ->offset($offset)
            ->get();

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $lastPage,
        ];
    }

    public function insert(array $data): bool
    {
        $this->assertTable();

        if ($data === []) {
            throw new RuntimeException('Insert data cannot be empty.');
        }

        [$sql, $bindings] = $this->compileInsert($data);

        return $this->db->execute($sql, $bindings);
    }

    public function insertGetId(array $data): int|string|false
    {
        $this->assertTable();

        if ($data === []) {
            throw new RuntimeException('Insert data cannot be empty.');
        }

        [$sql, $bindings] = $this->compileInsert($data);

        $ok = $this->db->execute($sql, $bindings);

        if (!$ok) {
            return false;
        }

        return $this->db->lastInsertId();
    }

    public function update(array $data): bool
    {
        $this->assertTable();

        if ($data === []) {
            throw new RuntimeException('Update data cannot be empty.');
        }

        $assignments = [];
        $bindings = [];

        foreach ($data as $column => $value) {
            $placeholder = ':upd_' . $column;
            $assignments[] = "{$column} = {$placeholder}";
            $bindings[$placeholder] = $value;
        }

        $sql = sprintf(
            'UPDATE %s SET %s%s',
            $this->table,
            implode(', ', $assignments),
            $this->compileWheres()
        );

        $bindings = array_merge($bindings, $this->getBindings());

        return $this->db->execute($sql, $bindings);
    }

    public function delete(): bool
    {
        $this->assertTable();

        $sql = sprintf(
            'DELETE FROM %s%s',
            $this->table,
            $this->compileWheres()
        );

        return $this->db->execute($sql, $this->getBindings());
    }

    public function toSql(): string
    {
        $this->assertTable();

        $sql = sprintf(
            'SELECT %s FROM %s',
            implode(', ', $this->columns),
            $this->table
        );

        if ($this->joins !== []) {
            $sql .= ' ' . implode(' ', $this->joins);
        }

        $sql .= $this->compileWheres();

        if ($this->orders !== []) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orders);
        }

        if ($this->limitValue !== null) {
            $sql .= ' LIMIT ' . $this->limitValue;
        }

        if ($this->offsetValue !== null) {
            $sql .= ' OFFSET ' . $this->offsetValue;
        }

        return $sql;
    }

    /**
     * @return array<string, mixed>
     */
    public function getBindings(): array
    {
        $bindings = [];

        foreach ($this->wheres as $where) {
            foreach ($where['bindings'] as $key => $value) {
                $bindings[$key] = $value;
            }
        }

        return $bindings;
    }

    /**
     * @return array{0:string,1:array<string,mixed>}
     */
    private function compileInsert(array $data): array
    {
        $columns = array_keys($data);
        $placeholders = array_map(
            static fn (string $column): string => ':' . $column,
            $columns
        );

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $bindings = [];
        foreach ($data as $column => $value) {
            $bindings[':' . $column] = $value;
        }

        return [$sql, $bindings];
    }

    private function compileWheres(): string
    {
        if ($this->wheres === []) {
            return '';
        }

        $sql = ' WHERE ';
        $first = true;

        foreach ($this->wheres as $where) {
            if (!$first) {
                $sql .= ' ' . $where['type'] . ' ';
            }

            $sql .= $where['sql'];
            $first = false;
        }

        return $sql;
    }

    private function assertTable(): void
    {
        if ($this->table === '') {
            throw new RuntimeException('No table selected for query builder.');
        }
    }

    /**
     * @return array{0:string,1:mixed}
     */
    private function normalizeOperatorAndValue(mixed $operator, mixed $value): array
    {
        if ($value === null && is_string($operator) && !$this->isOperator($operator)) {
            return ['=', $operator];
        }

        return [(string)$operator, $value];
    }

    private function isOperator(string $value): bool
    {
        return in_array(strtoupper($value), [
            '=', '!=', '<>', '>', '>=', '<', '<=', 'LIKE',
        ], true);
    }

    private function makePlaceholder(string $column, int $suffix): string
    {
        $column = preg_replace('/[^a-zA-Z0-9_]/', '_', $column) ?: 'param';
        return ':' . $column . '_' . $suffix;
    }
}