<?php

declare(strict_types=1);

namespace ZMosquita\Core\Http;

use ReflectionMethod;
use RuntimeException;
use ZMosquita\Core\Support\Container;

final class Dispatcher
{
    public function __construct(
        private Container $container,
        private MiddlewarePipeline $pipeline
    ) {
    }

    public function dispatch(Request $request, Route $route, array $params = []): mixed
    {
        $request = $request->withRouteParams($params);

        return $this->pipeline->handle(
            $request,
            $route->middlewares,
            function (Request $request) use ($route, $params) {
                return $this->invokeHandler($route->handler, $request, $params);
            }
        );
    }

    /**
     * @param callable|array{0:string,1:string} $handler
     * @param array<string,string> $params
     */
    private function invokeHandler(mixed $handler, Request $request, array $params): mixed
    {
        if (is_callable($handler) && !is_array($handler)) {
            return $handler($request);
        }

        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;

            if (!is_string($class) || !is_string($method)) {
                throw new RuntimeException('Invalid controller handler format.');
            }

            $controller = $this->container->has($class)
                ? $this->container->get($class)
                : new $class();

            $reflection = new ReflectionMethod($controller, $method);
            $arguments = [];

            foreach ($reflection->getParameters() as $parameter) {
                $name = $parameter->getName();

                if ($parameter->getType() && !$parameter->getType()->isBuiltin()) {
                    $typeName = $parameter->getType()->getName();

                    if ($typeName === Request::class) {
                        $arguments[] = $request;
                        continue;
                    }
                }

                if (array_key_exists($name, $params)) {
                    $value = $params[$name];
                    $type = $parameter->getType();

                    if ($type && $type->isBuiltin()) {
                        $typeName = $type->getName();
                        settype($value, $typeName);
                    }

                    $arguments[] = $value;
                    continue;
                }

                if ($parameter->isDefaultValueAvailable()) {
                    $arguments[] = $parameter->getDefaultValue();
                    continue;
                }

                throw new RuntimeException("Unable to resolve argument [{$name}] for handler.");
            }

            return $reflection->invokeArgs($controller, $arguments);
        }

        throw new RuntimeException('Unsupported route handler.');
    }
}