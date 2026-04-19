<?php

declare(strict_types=1);

namespace ZMosquita\Core\Database;

use PDOException;
use RuntimeException;
use ZMosquita\Core\Support\Config;

final class TenantConnectionResolver
{
    private array $connections = [];
    private ?Connection $iamConnection = null;
    private array $baseConfig;

    public function __construct(array $baseConfig)
    {
        $this->baseConfig = $baseConfig;
    }

    public function resolve(?string $catalog = null): Connection
    {
        if ($catalog === null || $catalog === '') {
            return $this->iam();
        }

        return $this->resolveForCatalog($catalog);
    }

    public function resolveForCatalog(string $catalog): Connection
    {
        if (isset($this->connections[$catalog])) {
            return $this->connections[$catalog];
        }

        return $this->connections[$catalog] = $this->createConnectionForCatalog($catalog);
    }

    public function iam(): Connection
    {
        if ($this->iamConnection !== null) {
            return $this->iamConnection;
        }

        $this->iamConnection = $this->createConnection($this->baseConfig);

        return $this->iamConnection;
    }

    public function hasConnection(string $catalog): bool
    {
        return isset($this->connections[$catalog]);
    }

    public function disconnectCatalog(string $catalog): void
    {
        unset($this->connections[$catalog]);
    }

    public function disconnectAll(): void
    {
        $this->connections = [];
        $this->iamConnection = null;
    }

    public function getActiveCatalogs(): array
    {
        return array_keys($this->connections);
    }

    public function count(): int
    {
        $count = count($this->connections);

        if ($this->iamConnection !== null) {
            $count++;
        }

        return $count;
    }

    private function createConnectionForCatalog(string $catalog): Connection
    {
        $config = $this->baseConfig;
        $config['database'] = $catalog;

        return $this->createConnection($config);
    }

    private function createConnection(array $config): Connection
    {
        try {
            return new Connection($config);
        } catch (PDOException $e) {
            throw new RuntimeException(
                "Failed to connect to database [{$config['database']}]: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    public function catalogExists(string $catalog): bool
    {
        try {
            $conn = $this->createConnectionForCatalog($catalog);
            $result = $conn->fetchOne("SELECT 1 AS exists");
            $this->disconnectCatalog($catalog);

            return $result !== null;
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'Unknown database')) {
                return false;
            }

            throw $e;
        }
    }
}
