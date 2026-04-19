<?php

namespace App\Core;
use App\Support\Sanitizer;
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

public function safe(string $key, string $filter = 'text', array $opts = []): mixed
    {
        $val = $this->input($key);

        return match ($filter) {
            'email'   => Sanitizer::email($val),
            'url'     => Sanitizer::url($val),
            'int'     => Sanitizer::int($val, $opts['min'] ?? null, $opts['max'] ?? null),
            'float'   => Sanitizer::float($val, $opts['min'] ?? null, $opts['max'] ?? null),
            'bool'    => Sanitizer::bool($val),
            'oneline' => Sanitizer::oneline($val, $opts['maxLen'] ?? 0),
            'slug'    => Sanitizer::slug((string)$val, $opts['maxLen'] ?? 80),
            default   => Sanitizer::text($val, $opts['maxLen'] ?? 0),
        };
    }

    /**
     * Devuelve todo el POST/GET filtrado con un esquema definido.
     * Ej:
     *   $schema = ['email'=>['filter'=>'email'], 'edad'=>['filter'=>'int','min'=>0,'max'=>120]];
     *   $req->allSafe($schema);
     */
    public function allSafe(array $schema): array
    {
        $merged = array_merge($this->get, $this->post);
        return Sanitizer::fromArray($merged, $schema);
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
