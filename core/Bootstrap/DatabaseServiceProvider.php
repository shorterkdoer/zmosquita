<?php

declare(strict_types=1);

namespace ZMosquita\Core\Bootstrap;

use ZMosquita\Core\Auth\ContextManager;
use ZMosquita\Core\Database\Connection;
use ZMosquita\Core\Database\DataDefMetaResolver;
use ZMosquita\Core\Database\DataDefResolver;
use ZMosquita\Core\Database\InitialSeedsResolver;
use ZMosquita\Core\Database\QueryBuilder;
use ZMosquita\Core\Database\Schema\AppSchemaInstaller;
use ZMosquita\Core\Database\Schema\CoreSchemaInstaller;
use ZMosquita\Core\Database\Schema\InitialSeedsInstaller;
use ZMosquita\Core\Database\Schema\SqlSchemaLoader;
use ZMosquita\Core\Database\Schema\TenantSchemaInstaller;
use ZMosquita\Core\Database\TableResolver;
use ZMosquita\Core\Database\TenantAwareConnection;
use ZMosquita\Core\Database\TenantConnectionResolver;
use ZMosquita\Core\Support\Config;

final class DatabaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->bind(TenantConnectionResolver::class, function ($c) {
            /** @var Config $config */
            $config = $c->get(Config::class);

            return new TenantConnectionResolver($config->getArray('database'));
        });

        $this->container->bind(Connection::class, function ($c) {
            $tenantResolver = $c->get(TenantConnectionResolver::class);
            $contextManager = $c->has(ContextManager::class)
                ? $c->get(ContextManager::class)
                : null;

            return new TenantAwareConnection($tenantResolver, $contextManager);
        });

        $this->container->bind(QueryBuilder::class, fn ($c) => new QueryBuilder(
            $c->get(Connection::class)
        ));

        $this->container->bind(TableResolver::class, function ($c) {
            $contextManager = $c->has(ContextManager::class)
                ? $c->get(ContextManager::class)
                : null;

            return new TableResolver($contextManager);
        });

        $this->container->set(DataDefResolver::class, new DataDefResolver());
        $this->container->set(InitialSeedsResolver::class, new InitialSeedsResolver());
        $this->container->set(DataDefMetaResolver::class, new DataDefMetaResolver());
        $this->container->set(SqlSchemaLoader::class, new SqlSchemaLoader());

        $this->container->bind(CoreSchemaInstaller::class, fn ($c) => new CoreSchemaInstaller(
            $c->get(Connection::class),
            $c->get(DataDefResolver::class),
            $c->get(SqlSchemaLoader::class)
        ));

        $this->container->bind(AppSchemaInstaller::class, fn ($c) => new AppSchemaInstaller(
            $c->get(Connection::class),
            $c->get(DataDefResolver::class),
            $c->get(SqlSchemaLoader::class)
        ));

        $this->container->bind(InitialSeedsInstaller::class, fn ($c) => new InitialSeedsInstaller(
            $c->get(Connection::class),
            $c->get(InitialSeedsResolver::class),
            $c->get(SqlSchemaLoader::class)
        ));

        $this->container->bind(TenantSchemaInstaller::class, fn ($c) => new TenantSchemaInstaller(
            $c->get(TenantConnectionResolver::class),
            $c->get(DataDefResolver::class),
            $c->get(InitialSeedsResolver::class),
            $c->get(SqlSchemaLoader::class)
        ));
    }
}