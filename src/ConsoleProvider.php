<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/seed
 * https://github.com/php-puff/seed/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace Puff\Seed;

use Psr\Container\ContainerInterface;
use Puff\Console\CommandProvider;

final class ConsoleProvider implements CommandProvider
{
    public function commands(string $root, ContainerInterface $container): iterable
    {
        $seeder = $container->get(Seeder::class);
        if (!$seeder instanceof Seeder) {
            throw new \LogicException('Seed service binding is invalid.');
        }

        yield new Command($seeder);
    }
}
