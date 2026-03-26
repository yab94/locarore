<?php

declare(strict_types=1);

namespace Rore\Framework;

use PDO;
use PDOException;

class Database extends PDO
{
    public function __construct(
        string $host,
        string $port,
        string $name,
        string $charset,
        string $user,
        string $password,
    ) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $host,
            $port,
            $name,
            $charset,
        );

        try {
            parent::__construct($dsn, $user, $password, [
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