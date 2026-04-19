<?php
// crear un query builder para facilitar la creación de consultas SQL
namespace App\Core\Helpers;
class QueryBuilder
{
    private string $table;
    private array $columns = [];
    private array $conditions = [];
    private array $orderBy = [];
    private int $limit = 0;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function select(array $columns): self
    {
        $this->columns = $columns;
        return $this;
    }

    public function where(string $condition, array $params): self
    {
        $this->conditions[] = ['condition' => $condition, 'params' => $params];
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy[] = "$column $direction";
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function build(): string
    {
        // Construir la consulta SQL
        $sql = "SELECT " . implode(', ', $this->columns) . " FROM " . $this->table;

        if (!empty($this->conditions)) {
            $sql .= " WHERE " . implode(' AND ', array_column($this->conditions, 'condition'));
        }

        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBy);
        }

        if ($this->limit > 0) {
            $sql .= " LIMIT " . $this->limit;
        }

        return $sql;
    }

    public function getParams(): array
    {
        // Recolectar todos los parámetros de las condiciones
        return array_merge(...array_column($this->conditions, 'params'));
    }
}

?>