<?php

declare(strict_types=1);

namespace ZMosquita\Core\Http\Middleware;

use ZMosquita\Core\Support\Facades\Auth;
use ZMosquita\Core\Support\Facades\Context;

final class RequireContext
{
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

        return $next($request);
    }
}