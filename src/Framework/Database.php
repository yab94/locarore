<?php

declare(strict_types=1);

namespace Rore\Framework;

use PDO;
use PDOException;
use Rore\Framework\Config;

class Database extends PDO
{
    public function __construct(Config $config)
    {
        $db  = $config->getArray('database');
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $db['host'],
            $db['port'],
            $db['name'],
            $db['charset'],
        );

        try {
            parent::__construct($dsn, $db['user'], $db['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die('Connexion BDD impossible : ' . $e->getMessage());
        }
    }
}
