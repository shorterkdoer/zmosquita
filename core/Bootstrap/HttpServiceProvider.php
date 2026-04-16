<?php

declare(strict_types=1);

namespace ZMosquita\Core\Bootstrap;

use ZMosquita\Core\Http\Dispatcher;
use ZMosquita\Core\Http\Kernel;
use ZMosquita\Core\Http\MiddlewarePipeline;
use ZMosquita\Core\Http\Router;

final class HttpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->set(Router::class, new Router());

        $this->container->bind(MiddlewarePipeline::class, fn ($c) => new MiddlewarePipeline(
            $c
        ));

        $this->container->bind(Dispatcher::class, fn ($c) => new Dispatcher(
            $c,
            $c->get(MiddlewarePipeline::class)
        ));

        $this->container->bind(Kernel::class, fn ($c) => new Kernel(
            $c->get(Router::class),
            $c->get(Dispatcher::class)
        ));
    }
}