<?php

namespace Foundation\Core;

class Request
{
    public string $method;
    public string $uri;
    public array $get;
    public array $post;
    public array $headers;
    public array $files;
    public array $server;

    public function __construct()
    {
        $this->method  = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri     = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->get     = $_GET;
        $this->post    = $_POST;
        $this->headers = getallheaders();
        $this->files   = $_FILES;
        $this->server  = $_SERVER;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    public function file(string $key): mixed
    {
        return $this->files[$key] ?? null;
    }

    public function header(string $key): mixed
    {
        return $this->headers[$key] ?? null;
    }

    public function is(string $method): bool
    {
        return strtoupper($this->method) === strtoupper($method);
    }
}
