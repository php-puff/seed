<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/seed
 * https://github.com/php-puff/seed/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace Puff\Seed;

use Puff\Console\Contract;
use Puff\Console\Input;
use Puff\Console\Output;

final readonly class Command implements Contract
{
    public function __construct(private Seeder $seeder)
    {
    }

    public function name(): string
    {
        return 'seed';
    }

    public function description(): string
    {
        return 'Run database seed files';
    }

    public function usage(): string
    {
        return 'seed <create|name|all> [name] [options]';
    }

    public function valueOptions(): array
    {
        return ['connection' => 'c'];
    }

    public function flagOptions(): array
    {
        return ['force' => 'f'];
    }

    public function execute(Input $input, Output $output): int
    {
        $action = (string) $input->argument(0);
        if ($action === '') {
            throw new \InvalidArgumentException('Seed action, name, or all is required.');
        }
        if ($action === 'create') {
            $name = (string) $input->argument(1);
            if ($name === '') {
                throw new \InvalidArgumentException('Seed name is required.');
            }
            $output->write('Generated seed [' . $this->seeder->create($name) . '].');

            return 0;
        }
        $this->guardProduction($input);
        $connection = $input->option('connection');
        $connection = \is_string($connection) ? $connection : null;
        if ($action === 'all') {
            $output->write('Ran ' . $this->seeder->all($connection) . ' seed file(s).');
            return 0;
        }
        $this->seeder->run($action, $connection);
        $output->write("Seeded [{$action}].");

        return 0;
    }

    private function guardProduction(Input $input): void
    {
        $environment = \strtolower((string) (\getenv('PUFF_ENV') ?: ''));
        if (\in_array($environment, ['prod', 'production'], true) && !$input->hasOption('force')) {
            throw new \RuntimeException('Use --force to run seeds when PUFF_ENV is production.');
        }
    }
}
