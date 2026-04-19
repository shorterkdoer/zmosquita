<?php

declare(strict_types=1);

namespace ZMosquita\Core\Database;

use ZMosquita\Core\Auth\ContextManager;

final class TableResolver
{
    public function __construct(
        private ?ContextManager $contextManager = null
    ) {
    }

    public function iam(string $table): string
    {
        return 'iam_' . ltrim($table, '_');
    }

    public function app(string $table, ?string $appCode = null): string
    {
        $code = $appCode ?: $this->currentAppCode();

        if (!$code) {
            throw new \RuntimeException("Cannot resolve app table [$table] without app code or active context.");
        }

        return $code . '_' . ltrim($table, '_');
    }

    public function qualify(string $scope, string $table, ?string $appCode = null): string
    {
        return $scope === 'core' || $scope === 'iam'
            ? $this->iam($table)
            : $this->app($table, $appCode);
    }

    public function currentAppCode(): ?string
    {
        return $this->contextManager?->app()['code'] ?? null;
    }
}