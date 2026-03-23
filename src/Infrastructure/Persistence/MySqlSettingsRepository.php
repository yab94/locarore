<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Persistence;

use Rore\Domain\Settings\Entity\Setting;
use Rore\Domain\Settings\Repository\SettingsRepositoryInterface;
use Rore\Infrastructure\Database\Connection;

class MySqlSettingsRepository implements SettingsRepositoryInterface
{
    /** Cache statique — chargé une seule fois par requête */
    private static ?array $cache = null;

    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::get();
    }

    /** @return Setting[] indexés par clé */
    public function findAll(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $stmt = $this->pdo->query('SELECT * FROM settings ORDER BY `group`, `key`');
        self::$cache = [];
        foreach ($stmt->fetchAll() as $row) {
            self::$cache[$row['key']] = $this->hydrate($row);
        }
        return self::$cache;
    }

    public function findByKey(string $key): ?Setting
    {
        return $this->findAll()[$key] ?? null;
    }

    public function save(Setting $setting): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO settings (`key`, `value`, `label`, `type`, `group`)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
        );
        $stmt->execute([
            $setting->getKey(),
            $setting->getValue(),
            $setting->getLabel(),
            $setting->getType(),
            $setting->getGroup(),
        ]);
        // Invalider le cache
        self::$cache = null;
    }

    /** Sauvegarde en masse (tableau key => value) */
    public function saveValues(array $keyValues): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE settings SET `value` = ? WHERE `key` = ?'
        );
        foreach ($keyValues as $key => $value) {
            $stmt->execute([$value, $key]);
        }
        self::$cache = null;
    }

    private function hydrate(array $row): Setting
    {
        return new Setting(
            key:   $row['key'],
            value: $row['value'],
            label: $row['label'],
            type:  $row['type'],
            group: $row['group'],
        );
    }
}
