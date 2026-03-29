<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Database;

use RRB\Database\Database;

final class MysqlDatabase extends Database
{
    public function __construct(
        string $host,
        string $port,
        string $name,
        string $charset,
        string $user,
        string $password,
    ) {
        parent::__construct($host, $port, $name, $charset, $user, $password);
    }
}
