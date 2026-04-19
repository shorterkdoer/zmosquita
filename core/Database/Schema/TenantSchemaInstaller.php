<?php

declare(strict_types=1);

namespace ZMosquita\Core\Database\Schema;

use ZMosquita\Core\Database\Connection;
use ZMosquita\Core\Database\DataDefResolver;
use ZMosquita\Core\Database\InitialSeedsResolver;
use ZMosquita\Core\Database\TenantConnectionResolver;

final class TenantSchemaInstaller
{
    public function __construct(
        private TenantConnectionResolver $connections,
        private DataDefResolver $dataDefResolver,
        private InitialSeedsResolver $seedsResolver,
        private SqlSchemaLoader $loader
    ) {
    }

    public function createTenantCatalog(string $catalog, array $options = []): bool
    {
        $charset = $options['charset'] ?? 'utf8mb4';
        $collation = $options['collation'] ?? 'utf8mb4_unicode_ci';

        $iam = $this->connections->iam();

        $sql = sprintf(
            "CREATE DATABASE `%s` CHARACTER SET %s COLLATE %s",
            $this->sanitizeCatalogName($catalog),
            $charset,
            $collation
        );

        try {
            $iam->execute($sql);
            return true;
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'database exists')) {
                return false;
            }
            throw $e;
        }
    }

    public function dropTenantCatalog(string $catalog, bool $force = false): bool
    {
        if (!$force) {
            throw new \RuntimeException(
                "Dropping catalog is dangerous. Use force:true to proceed."
            );
        }

        $iam = $this->connections->iam();

        $sql = sprintf("DROP DATABASE IF EXISTS `%s`", $this->sanitizeCatalogName($catalog));

        $this->connections->disconnectCatalog($catalog);

        return $iam->execute($sql);
    }

    public function tenantExists(string $catalog): bool
    {
        return $this->connections->catalogExists($catalog);
    }

    public function installCoreToTenant(string $catalog, bool $withSeeds = true): SchemaInstallResult
    {
        $connection = $this->connections->resolveForCatalog($catalog);

        $files = $this->dataDefResolver->allCore();
        $executedStatements = [];
        $errors = [];

        $connection->beginTransaction();

        try {
            foreach ($files as $file) {
                foreach ($this->loader->loadStatements($file) as $statement) {
                    $connection->execute($statement);
                    $executedStatements[] = $statement;
                }
            }

            if ($withSeeds && $this->seedsResolver->hasCoreSeeds()) {
                foreach ($this->seedsResolver->allCore() as $file) {
                    foreach ($this->loader->loadStatements($file) as $statement) {
                        $connection->execute($statement);
                        $executedStatements[] = $statement;
                    }
                }
            }

            $connection->commit();

            return SchemaInstallResult::success($files, $executedStatements);
        } catch (\Throwable $e) {
            $connection->rollBack();

            $errors[] = [
                'file' => '',
                'statement' => '',
                'error' => $e->getMessage(),
            ];

            return SchemaInstallResult::failure($files, $executedStatements, $errors);
        }
    }

    public function installAppToTenant(
        string $catalog,
        string $appCode,
        bool $withSeeds = true
    ): SchemaInstallResult {
        $connection = $this->connections->resolveForCatalog($catalog);

        $files = $this->dataDefResolver->allApp($appCode);
        $executedStatements = [];
        $errors = [];

        $connection->beginTransaction();

        try {
            foreach ($files as $file) {
                foreach ($this->loader->loadStatements($file) as $statement) {
                    $connection->execute($statement);
                    $executedStatements[] = $statement;
                }
            }

            if ($withSeeds && $this->seedsResolver->hasAppSeeds($appCode)) {
                foreach ($this->seedsResolver->allApp($appCode) as $file) {
                    foreach ($this->loader->loadStatements($file) as $statement) {
                        $connection->execute($statement);
                        $executedStatements[] = $statement;
                    }
                }
            }

            $connection->commit();

            return SchemaInstallResult::success($files, $executedStatements);
        } catch (\Throwable $e) {
            $connection->rollBack();

            $errors[] = [
                'file' => '',
                'statement' => '',
                'error' => $e->getMessage(),
            ];

            return SchemaInstallResult::failure($files, $executedStatements, $errors);
        }
    }

    private function sanitizeCatalogName(string $catalog): string
    {
        $catalog = preg_replace('/[^a-zA-Z0-9_]/', '', $catalog);

        if ($catalog === '') {
            throw new \InvalidArgumentException('Invalid catalog name');
        }

        return $catalog;
    }
}
