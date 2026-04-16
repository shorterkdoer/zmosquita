<?php

declare(strict_types=1);

namespace ZMosquita\Core\Http;

use RuntimeException;
use ZMosquita\Core\Support\Container;

final class MiddlewarePipeline
{
    public function __construct(
        private Container $container
    ) {
    }

    /**
     * @param array<int, string|object> $middlewares
     * @param callable $destination
     */
    public function handle(Request $request, array $middlewares, callable $destination): mixed
    {
        $pipeline = array_reduce(
            array_reverse($middlewares),
            function (callable $next, string|object $middleware): callable {
                return function (Request $request) use ($middleware, $next) {
                    $instance = is_string($middleware)
                        ? $this->container->get($middleware)
                        : $middleware;

                    if (!method_exists($instance, 'handle')) {
                        throw new RuntimeException('Middleware must implement handle()');
                    }

                    return $instance->handle($request, $next);
                };
            },
            $destination
        );

        return $pipeline($request);
    }
}