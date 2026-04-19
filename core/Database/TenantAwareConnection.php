<?php

declare(strict_types=1);

namespace ZMosquita\Core\Database;

use PDO;
use PDOStatement;
use ZMosquita\Core\Auth\ContextManager;

final class TenantAwareConnection
{
    public function __construct(
        private TenantConnectionResolver $resolver,
        private ?ContextManager $contextManager = null
    ) {
    }

    public function current(): Connection
    {
        $catalog = $this->getCurrentCatalog();

        return $this->resolver->resolve($catalog);
    }

    public function forTenant(int $tenantId): Connection
    {
        $tenant = $this->findTenantById($tenantId);

        if (!$tenant) {
            throw new \RuntimeException("Tenant [{$tenantId}] not found.");
        }

        return $this->resolver->resolveForCatalog($tenant['catalog']);
    }

    public function forCatalog(string $catalog): Connection
    {
        return $this->resolver->resolveForCatalog($catalog);
    }

    public function iam(): Connection
    {
        return $this->resolver->iam();
    }

    public function pdo(): PDO
    {
        return $this->current()->pdo();
    }

    public function prepare(string $sql): PDOStatement
    {
        return $this->current()->prepare($sql);
    }

    public function query(string $sql): PDOStatement
    {
        return $this->current()->query($sql);
    }

    public function execute(string $sql, array $params = []): bool
    {
        return $this->current()->execute($sql, $params);
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        return $this->current()->fetchOne($sql, $params);
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->current()->fetchAll($sql, $params);
    }

    public function beginTransaction(): bool
    {
        return $this->current()->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->current()->commit();
    }

    public function rollBack(): bool
    {
        return $this->current()->rollBack();
    }

    public function lastInsertId(): string|false
    {
        return $this->current()->lastInsertId();
    }

    private function getCurrentCatalog(): ?string
    {
        if ($this->contextManager === null) {
            return null;
        }

        $tenant = $this->contextManager->tenant();

        return $tenant['catalog'] ?? null;
    }

    private function findTenantById(int $tenantId): ?array
    {
        $iam = $this->iam();

        return $iam->fetchOne(
            "SELECT id, code, name, catalog
             FROM iam_tenants
             WHERE id = :id
               AND deleted_at IS NULL
               AND status = 'active'
             LIMIT 1",
            ['id' => $tenantId]
        );
    }
}
