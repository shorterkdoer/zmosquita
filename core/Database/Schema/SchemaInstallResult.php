<?php

declare(strict_types=1);

namespace ZMosquita\Core\Database\Schema;

final class SchemaInstallResult
{
    /**
     * @param string[] $files
     * @param string[] $statements
     * @param array<int, array{file:string, statement:string, error:string}> $errors
     */
    public function __construct(
        public bool $ok,
        public array $files = [],
        public array $statements = [],
        public array $errors = []
    ) {
    }

    public static function success(array $files, array $statements = []): self
    {
        return new self(true, $files, $statements, []);
    }

    public static function failure(array $files, array $statements, array $errors): self
    {
        return new self(false, $files, $statements, $errors);
    }
}