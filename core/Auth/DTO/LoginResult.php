<?php

declare(strict_types=1);

namespace ZMosquita\Core\Auth\DTO;

final class LoginResult
{
    public function __construct(
        public bool $ok,
        public ?int $userId = null,
        public ?string $message = null
    ) {
    }

    public static function ok(int $userId): self
    {
        return new self(true, $userId, null);
    }

    public static function fail(string $message): self
    {
        return new self(false, null, $message);
    }
}