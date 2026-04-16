<?php

declare(strict_types=1);

namespace ZMosquita\Core\Http\Middleware;

use ZMosquita\Core\Support\Facades\Auth;
use ZMosquita\Core\Support\Facades\Authz;
use ZMosquita\Core\Support\Facades\Context;

final class RequirePermission
{
    public function __construct(
        private string $permission
    ) {
    }

    public function handle(mixed $request, callable $next): mixed
    {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }

        if (!Context::isValid()) {
            header('Location: /select-context');
            exit;
        }

        if (!Authz::can($this->permission)) {
            http_response_code(403);
            echo 'Acceso denegado';
            exit;
        }

        return $next($request);
    }
}