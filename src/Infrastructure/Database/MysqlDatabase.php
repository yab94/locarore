<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Database;

use Rore\Framework\Database\Database;
use Rore\Framework\Di\BindConfig;

final class MysqlDatabase extends Database
{
    public function __construct(
        #[BindConfig('database.host')]
        string $host,
        #[BindConfig('database.port')]
        string $port,
        #[BindConfig('database.name')]
        string $name,
        #[BindConfig('database.charset')]
        string $charset,
        #[BindConfig('database.user')]
        string $user,
        #[BindConfig('database.password')]
        string $password,
    ) {
        parent::__construct($host, $port, $name, $charset, $user, $password);
    }
}
