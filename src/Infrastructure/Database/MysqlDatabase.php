<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Database;

use Rore\Framework\Bootstrap\Config;
use Rore\Framework\Database\Database;
use Rore\Framework\Di\Bind;

final class MysqlDatabase extends Database
{
    public function __construct(
        #[Bind(static function (Config $c): string { return $c->getString('database.host'); })]
        string $host,
        #[Bind(static function (Config $c): string { return $c->getString('database.port'); })]
        string $port,
        #[Bind(static function (Config $c): string { return $c->getString('database.name'); })]
        string $name,
        #[Bind(static function (Config $c): string { return $c->getString('database.charset'); })]
        string $charset,
        #[Bind(static function (Config $c): string { return $c->getString('database.user'); })]
        string $user,
        #[Bind(static function (Config $c): string { return $c->getString('database.password'); })]
        string $password,
    ) {
        parent::__construct($host, $port, $name, $charset, $user, $password);
    }
}
