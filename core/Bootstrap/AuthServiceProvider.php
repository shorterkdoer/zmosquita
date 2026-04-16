<?php

declare(strict_types=1);

namespace ZMosquita\Core\Bootstrap;

use ZMosquita\Core\Auth\AuditLogger;
use ZMosquita\Core\Auth\AuthManager;
use ZMosquita\Core\Auth\AuthorizationManager;
use ZMosquita\Core\Auth\ContextManager;
use ZMosquita\Core\Auth\ContextResolver;
use ZMosquita\Core\Auth\PasswordHasher;
use ZMosquita\Core\Auth\PermissionResolver;
use ZMosquita\Core\Auth\SessionGuard;
use ZMosquita\Core\Repositories\AppAccessRepository;
use ZMosquita\Core\Repositories\ApplicationRepository;
use ZMosquita\Core\Repositories\AuditLogRepository;
use ZMosquita\Core\Repositories\MembershipRepository;
use ZMosquita\Core\Repositories\PermissionRepository;
use ZMosquita\Core\Repositories\RoleRepository;
use ZMosquita\Core\Repositories\TenantRepository;
use ZMosquita\Core\Repositories\UserContextPreferenceRepository;
use ZMosquita\Core\Repositories\UserPermissionAssignmentRepository;
use ZMosquita\Core\Repositories\UserPermissionVersionRepository;
use ZMosquita\Core\Repositories\UserRepository;
use ZMosquita\Core\Repositories\UserRoleAssignmentRepository;
use ZMosquita\Core\Validation\Validator;
use ZMosquita\Core\Database\Connection;
use ZMosquita\Core\Database\TableResolver;
use ZMosquita\Core\Database\QueryBuilder;

final class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->set(SessionGuard::class, new SessionGuard());
        $this->container->set(PasswordHasher::class, new PasswordHasher());
        $this->container->set(Validator::class, new Validator());

        $this->container->bind(UserRepository::class, fn ($c) => new UserRepository(
            $c->get(QueryBuilder::class),
            $c->get(TableResolver::class)
        ));

        $this->container->bind(TenantRepository::class, fn ($c) => new TenantRepository(
            $c->get(QueryBuilder::class),
            $c->get(TableResolver::class)
        ));

        $this->container->bind(ApplicationRepository::class, fn ($c) => new ApplicationRepository(
            $c->get(QueryBuilder::class),
            $c->get(TableResolver::class)
        ));

        $this->container->bind(MembershipRepository::class, fn ($c) => new MembershipRepository(
            $c->get(QueryBuilder::class),
            $c->get(TableResolver::class)
        ));

        $this->container->bind(AppAccessRepository::class, fn ($c) => new AppAccessRepository(
            $c->get(QueryBuilder::class),
            $c->get(TableResolver::class)
        ));

        $this->container->bind(UserContextPreferenceRepository::class, fn ($c) => new UserContextPreferenceRepository(
            $c->get(QueryBuilder::class),
            $c->get(TableResolver::class)
        ));

        $this->container->bind(UserPermissionVersionRepository::class, fn ($c) => new UserPermissionVersionRepository(
            $c->get(QueryBuilder::class),
            $c->get(TableResolver::class)
        ));

        $this->container->bind(AuditLogRepository::class, fn ($c) => new AuditLogRepository(
            $c->get(QueryBuilder::class),
            $c->get(TableResolver::class)
        ));

        $this->container->bind(RoleRepository::class, fn ($c) => new RoleRepository(
            $c->get(QueryBuilder::class),
            $c->get(TableResolver::class)
        ));

        $this->container->bind(PermissionRepository::class, fn ($c) => new PermissionRepository(
            $c->get(QueryBuilder::class),
            $c->get(TableResolver::class)
        ));

        $this->container->bind(UserRoleAssignmentRepository::class, fn ($c) => new UserRoleAssignmentRepository(
            $c->get(QueryBuilder::class),
            $c->get(TableResolver::class)
        ));

        $this->container->bind(UserPermissionAssignmentRepository::class, fn ($c) => new UserPermissionAssignmentRepository(
            $c->get(QueryBuilder::class),
            $c->get(TableResolver::class)
        ));

        $this->container->bind(AuditLogger::class, fn ($c) => new AuditLogger(
            $c->get(AuditLogRepository::class)
        ));

        $this->container->bind(AuthManager::class, fn ($c) => new AuthManager(
            $c->get(UserRepository::class),
            $c->get(PasswordHasher::class),
            $c->get(SessionGuard::class),
            $c->get(AuditLogger::class)
        ));

        $this->container->bind(ContextResolver::class, fn ($c) => new ContextResolver(
            $c->get(MembershipRepository::class),
            $c->get(AppAccessRepository::class),
            $c->get(TenantRepository::class),
            $c->get(ApplicationRepository::class)
        ));

        $this->container->bind(ContextManager::class, fn ($c) => new ContextManager(
            $c->get(AuthManager::class),
            $c->get(ContextResolver::class),
            $c->get(SessionGuard::class),
            $c->get(UserContextPreferenceRepository::class),
            $c->get(UserPermissionVersionRepository::class),
            $c->get(AuditLogger::class)
        ));

        $this->container->bind(PermissionResolver::class, fn ($c) => new PermissionResolver(
            $c->get(RoleRepository::class),
            $c->get(UserRoleAssignmentRepository::class),
            $c->get(UserPermissionAssignmentRepository::class)
        ));

        $this->container->bind(AuthorizationManager::class, fn ($c) => new AuthorizationManager(
            $c->get(AuthManager::class),
            $c->get(ContextManager::class),
            $c->get(PermissionResolver::class),
            $c->get(SessionGuard::class)
        ));
    }
}