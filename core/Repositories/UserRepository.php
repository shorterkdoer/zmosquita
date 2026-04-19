<?php

declare(strict_types=1);

namespace ZMosquita\Core\Repositories;

use ZMosquita\Core\Database\QueryBuilder;
use ZMosquita\Core\Database\TableResolver;

final class UserRepository
{
    public function __construct(
        private QueryBuilder $qb,
        private TableResolver $tables
    ) {
    }

    public function findById(int $id): ?array
    {
        return $this->qb
            ->table($this->tables->iam('users'))
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();
    }

    public function findByIdentity(string $identity): ?array
    {
        return $this->qb
            ->table($this->tables->iam('users'))
            ->whereNull('deleted_at')
            ->whereRaw('(email = :identity OR username = :identity)', [
                ':identity' => $identity,
            ])
            ->first();
    }

    public function touchLastLogin(int $id): void
    {
        $this->qb
            ->table($this->tables->iam('users'))
            ->where('id', $id)
            ->update([
                'last_login_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function isActive(int $id): bool
    {
        $user = $this->findById($id);

        return $user !== null && ($user['status'] ?? null) === 'active';
    }
}