<?php

declare(strict_types=1);

namespace ZMosquita\Core\Http\Middleware;

use ZMosquita\Core\Support\Facades\Auth;

final class RequireAuth
{
    public function handle(mixed $request, callable $next): mixed
    {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }

        return $next($request);
    }
}