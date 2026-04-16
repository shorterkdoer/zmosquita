<?php

declare(strict_types=1);

namespace ZMosquita\Core\Http;

final class Router
{
    /** @var Route[] */
    private array $routes = [];

    /**
     * @param callable|array{0:string,1:string} $handler
     * @param array<int, string|object> $middlewares
     */
    public function get(string $path, mixed $handler, array $middlewares = []): void
    {
        $this->add('GET', $path, $handler, $middlewares);
    }

    /**
     * @param callable|array{0:string,1:string} $handler
     * @param array<int, string|object> $middlewares
     */
    public function post(string $path, mixed $handler, array $middlewares = []): void
    {
        $this->add('POST', $path, $handler, $middlewares);
    }

    /**
     * @param callable|array{0:string,1:string} $handler
     * @param array<int, string|object> $middlewares
     */
    public function add(string $method, string $path, mixed $handler, array $middlewares = []): void
    {
        $this->routes[] = new Route(
            method: strtoupper($method),
            path: $this->normalizePath($path),
            handler: $handler,
            middlewares: $middlewares
        );
    }

    /**
     * @return array{route:Route, params:array<string,string>}|null
     */
    public function match(Request $request): ?array
    {
        $method = strtoupper($request->method());
        $path = $this->normalizePath($request->path());

        foreach ($this->routes as $route) {
            if ($route->method !== $method) {
                continue;
            }

            $params = $this->matchPath($route->path, $path);
            if ($params !== null) {
                return [
                    'route' => $route,
                    'params' => $params,
                ];
            }
        }

        return null;
    }

    /**
     * @return Route[]
     */
    public function all(): array
    {
        return $this->routes;
    }

    /**
     * @return array<string,string>|null
     */
    private function matchPath(string $routePath, string $requestPath): ?array
    {
        $pattern = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            static fn (array $matches): string => '(?P<' . $matches[1] . '>[^/]+)',
            $routePath
        );

        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $requestPath, $matches)) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    private function normalizePath(string $path): string
    {
        $normalized = '/' . trim($path, '/');
        return $normalized === '//' ? '/' : $normalized;
    }
}