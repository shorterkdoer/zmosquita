<?php

namespace App\Middlewares;

use App\Enums\UserRole;
use Foundation\Core\Response;
use Foundation\Core\Session;
use Foundation\Middleware\BaseMiddleware;

/**
 * GuestMiddleware
 *
 * Middleware for routes that should only be accessible by unauthenticated users.
 * Redirects authenticated users to the dashboard.
 */
class GuestMiddleware extends BaseMiddleware
{
    /**
     * Handle the middleware check
     *
     * If user is already authenticated, redirect to dashboard.
     * Otherwise, allow access to guest-only routes (login, register, etc.).
     */
    public function handle(): void
    {
        // Ensure session is started
        Session::start();

        // If user is already authenticated, redirect to dashboard
        if (Session::isAuthenticated()) {
            Response::redirect('/dashboard');
        }

        // User is not authenticated, allow access to guest routes
    }
}
