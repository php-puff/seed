<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/seed
 * https://github.com/php-puff/seed/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace Puff\Seed\Tests;

use PHPUnit\Framework\TestCase;
use Puff\Database\Contract\ConnectionInterface;
use Puff\Database\Contract\DatabaseManagerInterface;
use Puff\Seed\Seeder;

final class SeederTest extends TestCase
{
    public function testRunsNamedAndAllSeeds(): void
    {
        $directory = \sys_get_temp_dir() . '/puff-seed-' . \bin2hex(\random_bytes(6));
        self::assertTrue(\mkdir($directory));
        $source = <<<'PHP'
<?php

declare(strict_types=1);

return static function (\Puff\Database\Contract\ConnectionInterface $connection): void {
    $connection->execute('SELECT 1');
};
PHP;
        self::assertNotFalse(\file_put_contents($directory . '/Users.php', $source));
        self::assertNotFalse(\file_put_contents($directory . '/Roles.php', $source));
        $connection = $this->createMock(ConnectionInterface::class);
        $connection->expects(self::exactly(3))->method('execute')->with('SELECT 1');
        $databases = $this->createMock(DatabaseManagerInterface::class);
        $databases->method('connection')->willReturn($connection);
        $seeder = new Seeder($databases, $directory);

        $seeder->run('Users');
        self::assertSame(2, $seeder->all());

        \unlink($directory . '/Users.php');
        \unlink($directory . '/Roles.php');
        \rmdir($directory);
    }

    public function testCreatesSeedFromStub(): void
    {
        $directory = \sys_get_temp_dir() . '/puff-seed-' . \bin2hex(\random_bytes(6));
        $databases = $this->createMock(DatabaseManagerInterface::class);
        $seeder = new Seeder($databases, $directory);

        $path = $seeder->create('Users');
        self::assertSame($directory . '/Users.php', $path);
        $source = (string) \file_get_contents($path);
        self::assertStringContainsString('ConnectionInterface', $source);
        self::assertStringContainsString("table('users')", $source);

        \unlink($path);
        \rmdir($directory);
    }
}
