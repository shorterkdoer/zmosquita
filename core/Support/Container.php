<?php

declare(strict_types=1);

namespace ZMosquita\Core\Support;

use Closure;
use RuntimeException;

final class Container
{
    private static ?self $instance = null;

    /** @var array<string, mixed> */
    private array $bindings = [];

    /** @var array<string, object> */
    private array $instances = [];

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function bind(string $abstract, mixed $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, mixed $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function set(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function has(string $abstract): bool
    {
        return isset($this->instances[$abstract]) || isset($this->bindings[$abstract]);
    }

    public function get(string $abstract): object
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (!isset($this->bindings[$abstract])) {
            throw new RuntimeException("No service bound for [$abstract].");
        }

        $concrete = $this->bindings[$abstract];

        if ($concrete instanceof Closure) {
            $object = $concrete($this);
        } elseif (is_string($concrete)) {
            $object = new $concrete();
        } else {
            $object = $concrete;
        }

        if (!is_object($object)) {
            throw new RuntimeException("Resolved service [$abstract] is not an object.");
        }

        $this->instances[$abstract] = $object;

        return $object;
    }

    public function reset(): void
    {
        $this->bindings = [];
        $this->instances = [];
    }
}