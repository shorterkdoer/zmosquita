<?php

declare(strict_types=1);

namespace ZMosquita\Core\Generators\Shared;

final class ColumnDefinition
{
    public function __construct(
        public string $name,
        public string $type,
        public bool $nullable = false,
        public bool $primaryKey = false,
        public bool $autoIncrement = false,
        public mixed $default = null,
        public ?int $length = null,
        public array $meta = [],
    ) {
    }

    public function isTimestamp(): bool
    {
        return in_array($this->name, ['created_at', 'updated_at', 'deleted_at'], true);
    }

    public function isForeignKey(): bool
    {
        return isset($this->meta['foreign_key']) && $this->meta['foreign_key'] === true;
    }
}