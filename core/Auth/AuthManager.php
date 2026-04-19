<?php

declare(strict_types=1);

namespace ZMosquita\Core\Auth;

use RuntimeException;
use ZMosquita\Core\Auth\DTO\LoginResult;
use ZMosquita\Core\Repositories\UserRepository;

final class AuthManager
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $passwordHasher,
        private SessionGuard $session,
        private AuditLogger $audit
    ) {
    }

    public function login(string $identity, string $password): LoginResult
    {
        $user = $this->users->findByIdentity($identity);

        if (!$user) {
            $this->audit->authLoginFailure($identity);
            return LoginResult::fail('Credenciales inválidas.');
        }

        if (($user['status'] ?? null) !== 'active') {
            $this->audit->authLoginFailure($identity, [
                'payload_json' => ['reason' => 'inactive_user']
            ]);
            return LoginResult::fail('Usuario no habilitado.');
        }

        if (!$this->passwordHasher->verify($password, $user['password_hash'])) {
            $this->audit->authLoginFailure($identity, [
                'subject_user_id' => (int)$user['id'],
                'payload_json'    => ['reason' => 'bad_password']
            ]);
            return LoginResult::fail('Credenciales inválidas.');
        }

        $this->session->regenerate();
        $this->setUserSession((int)$user['id']);
        $this->users->touchLastLogin((int)$user['id']);
        $this->audit->authLoginSuccess((int)$user['id']);

        return LoginResult::ok((int)$user['id']);
    }

    public function logout(): void
    {
        $userId = $this->id();
        $this->clearUserSession();
        $this->session->remove('context');
        $this->session->remove('permissions');

        if ($userId !== null) {
            $this->audit->authLogout($userId);
        }
    }

    public function check(): bool
    {
        $userId = $this->id();

        return $userId !== null && $this->users->isActive($userId);
    }

    public function id(): ?int
    {
        $userId = $this->session->get('auth.user_id');

        return is_numeric($userId) ? (int)$userId : null;
    }

    public function user(): ?array
    {
        $userId = $this->id();

        return $userId ? $this->users->findById($userId) : null;
    }

    public function userOrFail(): array
    {
        $user = $this->user();

        if (!$user) {
            throw new RuntimeException('No authenticated user.');
        }

        return $user;
    }

    public function setUserSession(int $userId): void
    {
        $this->session->set('auth.user_id', $userId);
        $this->session->set('auth.logged_in_at', date('Y-m-d H:i:s'));
    }

    public function clearUserSession(): void
    {
        $this->session->remove('auth');
    }

    public function refreshUser(): ?array
    {
        return $this->user();
    }
}