<?php

namespace App\Middlewares;

use App\Enums\UserRole;
use Foundation\Core\Session;
use Foundation\Middleware\BaseMiddleware;
use Foundation\Core\Response;

/**
 * AdminMiddleware
 *
 * Authorization middleware that checks if the authenticated user
 * has administrative privileges (admin or superuser).
 */
class AdminMiddleware extends BaseMiddleware
{
    /**
     * Handle the middleware check
     *
     * Verifies that:
     * 1. User is authenticated
     * 2. User has admin or superuser role
     *
     * Returns 401 if not authenticated, 403 if not admin/superuser.
     */
    public function handle(): void
    {
        // Ensure session is started
        Session::start();

        // Check if user exists in session
        if (!Session::isAuthenticated()) {
            http_response_code(401);
            echo '<div class="alert alert-danger m-3">Acceso no autorizado. Inicie sesión.</div>';
            exit;
        }

        $user = Session::user();
        if (!$user) {
            http_response_code(401);
            echo '<div class="alert alert-danger m-3">Sesión inválida. Inicie sesión nuevamente.</div>';
            exit;
        }

        // Get user role
        $roleString = $user['role'] ?? 'guest';

        // Validate and convert to enum
        $role = UserRole::isValid($roleString)
            ? UserRole::from($roleString)
            : UserRole::GUEST;

        // Check if user has admin privileges (admin or superuser)
        if (!$role->isAdmin()) {
            http_response_code(403);
            echo '<div class="alert alert-warning m-3">
                <h4><i class="bi bi-exclamation-triangle"></i> Acceso Restringido</h4>
                <p>Esta sección requiere permisos de administrador.</p>
                <p>Tu rol actual: <strong>' . htmlspecialchars($role->getLabel()) . '</strong></p>
                <a href="/dashboard" class="btn btn-primary">Volver al Dashboard</a>
            </div>';
            exit;
        }
    }
}
