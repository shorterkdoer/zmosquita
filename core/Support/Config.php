<?php

declare(strict_types=1);

namespace ZMosquita\Core\Support;

use RuntimeException;

final class Config
{
    /**
     * @param array<string, mixed> $items
     */
    public function __construct(
        private array $items = []
    ) {
    }

    public function all(): array
    {
        return $this->items;
    }

    public function has(string $key): bool
    {
        return $this->get($key, '__missing__') !== '__missing__';
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = $this->items;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    public function getString(string $key, ?string $default = null): ?string
    {
        $value = $this->get($key, $default);

        return $value === null ? null : (string)$value;
    }

    public function getArray(string $key, array $default = []): array
    {
        $value = $this->get($key, $default);

        return is_array($value) ? $value : $default;
    }

    public function require(string $key): mixed
    {
        if (!$this->has($key)) {
            throw new RuntimeException("Missing configuration key [{$key}]");
        }

        return $this->get($key);
    }
}