<?php

declare(strict_types=1);

namespace ZMosquita\Core\Http\Controllers;

use ZMosquita\Core\Database\QueryBuilder;
use ZMosquita\Core\Support\Container;
use ZMosquita\Core\Support\Facades\Auth;
use ZMosquita\Core\Support\Facades\Authz;
use ZMosquita\Core\Support\Facades\Context;

abstract class BaseController
{
    protected function user(): ?array
    {
        return Auth::user();
    }

    protected function userId(): ?int
    {
        return Auth::id();
    }

    protected function tenant(): ?array
    {
        return Context::tenant();
    }

    protected function app(): ?array
    {
        return Context::app();
    }

    protected function can(string $permission): bool
    {
        return Authz::can($permission);
    }

    protected function authorize(string $permission): void
    {
        if (!Authz::can($permission)) {
            $this->abort(403, 'Acceso denegado');
        }
    }

    protected function db(): QueryBuilder
    {
        return Container::instance()->get(QueryBuilder::class);
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require $view;
    }

    protected function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    protected function abort(int $statusCode, string $message = ''): never
    {
        http_response_code($statusCode);
        echo $message;
        exit;
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function allInput(): array
    {
        return array_merge($_GET, $_POST);
    }
}