<?php

declare(strict_types=1);

namespace ZMosquita\Core\Generators\Shared;

final class ForeignKeyDefinition
{
    public function __construct(
        public string $column,
        public string $referencedTable,
        public string $referencedColumn = 'id',
        public ?string $constraintName = null,
        public array $meta = [],
    ) {
    }
}