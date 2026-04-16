<?php

declare(strict_types=1);

namespace ZMosquita\Core\Http\Controllers;

use ZMosquita\Core\Support\Facades\Auth;
use ZMosquita\Core\Support\Facades\Context;

final class AuthController
{
    public function showLogin(): void
    {
        echo '<h1>Login</h1>';
        echo '<form method="post" action="/login">';
        echo '<input type="text" name="identity" placeholder="Email o usuario">';
        echo '<input type="password" name="password" placeholder="Contraseña">';
        echo '<button type="submit">Ingresar</button>';
        echo '</form>';
    }

    public function login(): void
    {
/*
        $identity = trim((string)($_POST['identity'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        $result = Auth::login($identity, $password);

        if (!$result->ok) {
            http_response_code(401);
            echo $result->message ?? 'Error de autenticación';
            return;
        }

        // 1) último contexto preferido
        if (Context::service()->restorePreferredContext()) {
            header('Location: /dashboard');
            exit;
        }

        // 2) único contexto disponible
        if (Context::service()->resolveSingleContext()) {
            header('Location: /dashboard');
            exit;
        }

        // 3) múltiples contextos
        header('Location: /select-context');
        exit;
*/
if (Context::restorePreferredContext()) {
    header('Location: /dashboard');
    exit;
}

if (Context::resolveSingleContext()) {
    header('Location: /dashboard');
    exit;
}
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: /login');
        exit;
    }

    public function showContextSelector(): void
    {
        $contexts = Context::availableContexts();

        echo '<h1>Seleccionar contexto</h1>';
        echo '<form method="post" action="/select-context">';
        echo '<select name="context_key">';

        foreach ($contexts as $index => $ctx) {
            $label = htmlspecialchars($ctx->tenantName . ' / ' . $ctx->appName, ENT_QUOTES, 'UTF-8');
            $value = $ctx->tenantId . ':' . $ctx->appId;
            echo "<option value=\"{$value}\">{$label}</option>";
        }

        echo '</select>';
        echo '<button type="submit">Continuar</button>';
        echo '</form>';
    }

    public function selectContext(): void
    {
        $raw = (string)($_POST['context_key'] ?? '');
        [$tenantId, $appId] = array_pad(explode(':', $raw, 2), 2, null);

        $tenantId = is_numeric($tenantId) ? (int)$tenantId : null;
        $appId = is_numeric($appId) ? (int)$appId : null;

        if (!$tenantId || !$appId) {
            http_response_code(422);
            echo 'Contexto inválido';
            return;
        }

        if (!Context::switch($tenantId, $appId)) {
            http_response_code(403);
            echo 'No tenés acceso a ese contexto';
            return;
        }

        header('Location: /dashboard');
        exit;
    }
}