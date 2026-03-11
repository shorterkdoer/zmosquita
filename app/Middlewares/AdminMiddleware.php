<?php

namespace App\Middlewares;

use Foundation\Middleware\BaseMiddleware;

class AdminMiddleware extends BaseMiddleware
{
    public function handle(): void
    {
        // Asegurarse de que la sesión esté iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Comprobar que exista el usuario en la sesión
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo "Acceso no autorizado. Inicie sesión.";
            exit;
        }

        // Se espera que en la sesión se guarde un array con información del usuario
        // Ejemplo: $_SESSION['user'] = ['id' => 1, 'email' => 'admin@example.com', 'role' => 'admin'];
        $user = $_SESSION['user'];
        if (!isset($user['role']) || $user['role'] !== 'admin') {
            http_response_code(403);
            echo "Acceso restringido. Se requieren permisos de administrador.";
            exit;
        }
    }
}
