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

        // Get current application namespace
        $appNamespace = $_SESSION['current_app_namespace'] ?? 'App';

        foreach (self::$routes as $route) {
            $pattern = "@^" . preg_replace('/\{[^\}]+\}/', '([^/]+)', $route['uri']) . "$@";

            if ($route['method'] === $requestMethod && preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches);

                // Ejecutar middlewares
                foreach ($route['middlewares'] as $middleware) {
                    // Resolve middleware namespace if not fully qualified
                    $middlewareClass = $middleware;
                    if (strpos($middleware, '\\') === false) {
                        // Try current app namespace first, then App namespace
                        $middlewareClass = "$appNamespace\\Middlewares\\$middleware";
                        if (!class_exists($middlewareClass)) {
                            $middlewareClass = "App\\Middlewares\\$middleware";
                        }
                    }

                    if (class_exists($middlewareClass)) {
                        $middlewareInstance = new $middlewareClass();
                        if (method_exists($middlewareInstance, 'handle')) {
                            $middlewareInstance->handle();
                        }
                    }
                }

                [$controller, $method] = $route['action'];

                // Resolve controller namespace if not fully qualified
                $controllerClass = $controller;
                if (strpos($controller, '\\') === false) {
                    // Try current app namespace first
                    $controllerClass = "$appNamespace\\Controllers\\$controller";
                    if (!class_exists($controllerClass)) {
                        // Fall back to App namespace for shared controllers
                        $controllerClass = "App\\Controllers\\$controller";
                    }
                }

                if (!class_exists($controllerClass)) {
                    http_response_code(500);
                    echo "Controlador no encontrado: $controllerClass";
                    return;
                }

                $controllerInstance = new $controllerClass();

                if (!method_exists($controllerInstance, $method)) {
                    http_response_code(500);
                    echo "Método no encontrado: $method en $controllerClass";
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
