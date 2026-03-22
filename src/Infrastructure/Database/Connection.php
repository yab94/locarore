<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Database;

use PDO;
use PDOException;

class Connection
{
    private static ?PDO $pdo = null;

    public static function init(array $config): void
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['name'],
            $config['charset'],
        );

        try {
            self::$pdo = new PDO($dsn, $config['user'], $config['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die('Connexion BDD impossible : ' . $e->getMessage());
        }
    }

    public static function get(): PDO
    {
        if (self::$pdo === null) {
            throw new \RuntimeException('La connexion BDD n\'a pas été initialisée.');
        }
        return self::$pdo;
    }
}
