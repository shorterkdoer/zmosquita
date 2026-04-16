<?php

declare(strict_types=1);

namespace ZMosquita\Core\Generators\Shared;

use InvalidArgumentException;

final class GeneratorContext
{
    public function __construct(
        public string $scope,
        public ?string $appCode,
        public string $resourceName,
        public bool $force = false,
        public bool $dryRun = false,
    ) {
        if (!in_array($this->scope, ['core', 'app'], true)) {
            throw new InvalidArgumentException("Invalid generator scope [{$this->scope}]");
        }

        if ($this->scope === 'app' && (!$this->appCode || trim($this->appCode) === '')) {
            throw new InvalidArgumentException('App scope requires appCode.');
        }

        if (trim($this->resourceName) === '') {
            throw new InvalidArgumentException('Generator resourceName cannot be empty.');
        }
    }

    public function isCore(): bool
    {
        return $this->scope === 'core';
    }

    public function isApp(): bool
    {
        return $this->scope === 'app';
    }

    public function qualifiedName(): string
    {
        return $this->isCore()
            ? "core:{$this->resourceName}"
            : "app:{$this->appCode}:{$this->resourceName}";
    }

    public function shouldForce(): bool
    {
        return $this->force;
    }

    public function shouldDryRun(): bool
    {
        return $this->dryRun;
    }
}