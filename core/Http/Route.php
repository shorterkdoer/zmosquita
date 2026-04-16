<?php

declare(strict_types=1);

namespace ZMosquita\Core\Http;

final class Route
{
    /**
     * @param callable|array{0:string,1:string} $handler
     * @param array<int, string|object> $middlewares
     */
    public function __construct(
        public string $method,
        public string $path,
        public mixed $handler,
        public array $middlewares = []
    ) {
    }
}