<?php

declare(strict_types=1);

namespace Rore\Settings\Adapter;

use Rore\Shared\Infrastructure\MysqlDatabase;
use Rore\Settings\Entity\Setting;
use Rore\Settings\Port\SettingsRepositoryInterface;

class MySqlSettingsRepository implements SettingsRepositoryInterface
{
    /** Cache statique — chargé une seule fois par requête */
    private static ?array $cache = null;



    public function __construct(
        private readonly MysqlDatabase $connection
    ) {}

    /** @return Setting[] indexés par clé */
    public function findAll(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $stmt = $this->connection->query('SELECT * FROM settings ORDER BY `group`, `key`');
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
        $stmt = $this->connection->prepare(
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
        $stmt = $this->connection->prepare(
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
