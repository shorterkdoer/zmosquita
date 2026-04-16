<?php

declare(strict_types=1);

namespace ZMosquita\Core\Http;

final class Request
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $request
     * @param array<string, mixed> $server
     * @param array<string, mixed> $files
     * @param array<string, mixed> $cookies
     * @param array<string, string> $routeParams
     */
    public function __construct(
        private string $method,
        private string $path,
        private array $query = [],
        private array $request = [],
        private array $server = [],
        private array $files = [],
        private array $cookies = [],
        private array $routeParams = [],
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        return new self(
            method: $method,
            path: $path,
            query: $_GET,
            request: $_POST,
            server: $_SERVER,
            files: $_FILES,
            cookies: $_COOKIE,
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->normalizePath($this->path);
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->request[$key] ?? $this->query[$key] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge($this->query, $this->request);
    }

    public function route(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    /**
     * @param array<string, string> $params
     */
    public function withRouteParams(array $params): self
    {
        return new self(
            method: $this->method,
            path: $this->path,
            query: $this->query,
            request: $this->request,
            server: $this->server,
            files: $this->files,
            cookies: $this->cookies,
            routeParams: $params,
        );
    }

    private function normalizePath(string $path): string
    {
        $normalized = '/' . trim($path, '/');
        return $normalized === '//' ? '/' : $normalized;
    }
}