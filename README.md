# Puff Seed

`puff/seed` runs explicit, repeatable database seed files. It is separate from `puff/migration`: migrations manage DDL and version history, while seeds initialise or refresh data.

Install it for development:

```bash
composer require --dev puff/seed
```

The package publishes `config/seed.php` with a default `seed/` directory.

Create `seed/Users.php`:

```bash
./puff seed create Users
```

The generated file is:

```php
<?php

declare(strict_types=1);

use Puff\Database\Contract\ConnectionInterface;

return static function (ConnectionInterface $database): void {
    $database->table('users')->insert([
        'name' => 'Admin',
        'email' => 'admin@example.com',
    ]);
};
```

Run one seed or all seed files alphabetically:

```bash
./puff seed Users
./puff seed all
./puff seed all --connection=mysql
```

Seed files must return a callable accepting `ConnectionInterface`. Make every seed idempotent; this component does not track seed history. In production, add `--force` explicitly.
