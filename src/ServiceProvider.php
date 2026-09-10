<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/seed
 * https://github.com/php-puff/seed/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace Puff\Seed;

use Puff\Config\Config;
use Puff\Database\Contract\DatabaseManagerInterface;
use Puff\Di\Container;
use Puff\Di\ServiceProvider as BaseServiceProvider;

final class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        if (!$this->app->bound(Config::class) || !$this->app->bound(DatabaseManagerInterface::class)) {
            return;
        }
        $config = $this->app->make(Config::class)->get('seed', []);
        $path = \is_array($config) ? (string) ($config['path'] ?? 'seed') : 'seed';
        $this->app->singleton(Seeder::class, static function (Container $container) use ($path): Seeder {
            $databases = $container->make(DatabaseManagerInterface::class);
            if (!$databases instanceof DatabaseManagerInterface) {
                throw new \RuntimeException('Database manager service is invalid.');
            }

            return new Seeder($databases, $path);
        });
    }
}
