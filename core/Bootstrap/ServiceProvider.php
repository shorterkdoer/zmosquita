<?php

declare(strict_types=1);

namespace ZMosquita\Core\Bootstrap;

use ZMosquita\Core\Support\Container;

abstract class ServiceProvider
{
    public function __construct(
        protected Container $container
    ) {
    }

    abstract public function register(): void;

    public function boot(): void
    {
        // Opcional para providers que quieran ejecutar lógica post-registro.
    }
}