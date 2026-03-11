<?php

namespace Foundation\Core;

class Router
{
    private static array $routes = [];

    public static function get(string $uri, array $action, array $middlewares = []): void
    {
        self::addRoute('GET', $uri, $action, $middlewares);
    }

    public static function post(string $uri, array $action, array $middlewares = []): void
    {
        self::addRoute('POST', $uri, $action, $middlewares);
    }

    public static function addRoute(string $method, string $uri, array $action, array $middlewares = []): void
    {
        self::$routes[] = [
            'method' => strtoupper($method),
            'uri' => self::normalizeUri($uri),
            'action' => $action,
            'middlewares' => $middlewares
        ];
    }

    public static function dispatch(string $requestUri, string $requestMethod): void
    {
        $requestUri = self::normalizeUri(parse_url($requestUri, PHP_URL_PATH));


        foreach (self::$routes as $route) {
            $pattern = "@^" . preg_replace('/\{[^\}]+\}/', '([^/]+)', $route['uri']) . "$@";

            if ($route['method'] === $requestMethod && preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches);

                // Ejecutar middlewares
                foreach ($route['middlewares'] as $middleware) {
                    $middlewareInstance = new $middleware();
                    if (method_exists($middlewareInstance, 'handle')) {
                        $middlewareInstance->handle();
                    }
                }

                [$controller, $method] = $route['action'];
                $controllerInstance = new $controller();

                if (!method_exists($controllerInstance, $method)) {
                    echo "Método no encontrado: $method";
                    http_response_code(500);
                    echo "Método no encontrado: $method";
                    return;
                }
                $request = new Request();

                call_user_func_array([$controllerInstance, $method], [$request, $matches]);
                return;
            }
        }

        http_response_code(404);
        echo "Ruta no encontrada";
    }

    private static function normalizeUri(string $uri): string
    {
        return '/' . trim($uri, '/');
    }
}
