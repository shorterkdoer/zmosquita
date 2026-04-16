<?php

declare(strict_types=1);

namespace ZMosquita\Core\Bootstrap;

use ZMosquita\Core\Support\Container;
use ZMosquita\Core\Support\Config;
use ZMosquita\Core\Support\Paths;

final class ApplicationBootstrap
{
    /** @var class-string<ServiceProvider>[] */
    private array $providers = [];

    public function __construct(
        private Container $container
    ) {
    }

    /**
     * @param array<string, mixed> $config
     * @param class-string<ServiceProvider>[] $providers
     */
    public function bootstrap(string $basePath, array $config, array $providers): void
    {
        Paths::setBasePath($basePath);

        $this->container->set(Config::class, new Config($config));

        $this->providers = $providers;

        foreach ($this->providers as $providerClass) {
            $provider = new $providerClass($this->container);
            $provider->register();
            $provider->boot();
        }
    }
}