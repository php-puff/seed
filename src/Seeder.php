<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/seed
 * https://github.com/php-puff/seed/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace Puff\Seed;

use Puff\Database\Contract\ConnectionInterface;
use Puff\Database\Contract\DatabaseManagerInterface;

final readonly class Seeder
{
    public function __construct(
        private DatabaseManagerInterface $databases,
        private string $directory,
    ) {
    }

    public function run(string $name, ?string $connection = null): void
    {
        $this->seed($this->path($name), $this->databases->connection($connection));
    }

    public function create(string $name): string
    {
        $this->validate($name);
        if (!\is_dir($this->directory) && !@\mkdir($this->directory, 0777, true) && !\is_dir($this->directory)) {
            throw new \RuntimeException("Unable to create seed directory [{$this->directory}].");
        }
        $path = $this->file($name);
        if (\is_file($path)) {
            throw new \InvalidArgumentException("Seed [{$name}] already exists.");
        }
        $stub = \file_get_contents(\dirname(__DIR__) . '/stub/seed.stub');
        $source = $stub === false ? false : \str_replace('%TABLE%', $this->table($name), $stub);
        if ($source === false || \file_put_contents($path, $source, LOCK_EX) === false) {
            throw new \RuntimeException("Unable to create seed [{$name}].");
        }

        return $path;
    }

    public function all(?string $connection = null): int
    {
        $files = \glob(\rtrim($this->directory, '/\\') . '/*.php') ?: [];
        \sort($files);
        $database = $this->databases->connection($connection);
        foreach ($files as $file) {
            $this->seed($file, $database);
        }

        return \count($files);
    }

    private function path(string $name): string
    {
        $this->validate($name);
        $path = $this->file($name);
        if (!\is_file($path)) {
            throw new \InvalidArgumentException("Seed [{$name}] does not exist.");
        }

        return $path;
    }

    private function file(string $name): string
    {
        return \rtrim($this->directory, '/\\') . '/' . $name . '.php';
    }

    private function validate(string $name): void
    {
        if (!\preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $name)) {
            throw new \InvalidArgumentException("Invalid seed name [{$name}].");
        }
    }

    private function table(string $name): string
    {
        $snake = \strtolower((string) \preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
        return \str_ends_with($snake, 's') ? $snake : $snake . 's';
    }

    private function seed(string $path, ConnectionInterface $connection): void
    {
        $seed = require $path;
        if (!\is_callable($seed)) {
            throw new \UnexpectedValueException("Seed [{$path}] must return a callable.");
        }
        $seed($connection);
    }
}
