<?php

namespace App\Middlewares;

use Foundation\Middleware\BaseMiddleware;

class GuestMiddleware extends BaseMiddleware
{
    public function handle(): void
    {
        // Aseguramos que la sesión esté iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Si no hay usuario logueado, permite continuar (mostrar el login o registro).
    }
}
