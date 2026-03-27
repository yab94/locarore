<?php

declare(strict_types=1);

namespace Rore\Domain\Settings\Repository;

use Rore\Domain\Settings\Entity\Setting;
use Rore\Infrastructure\Persistence\MySqlSettingsRepository;

interface SettingsRepositoryInterface
{
    /** @return Setting[] indexés par clé */
    public function findAll(): array;

    public function findByKey(string $key): ?Setting;

    public function save(Setting $setting): void;

    /** Sauvegarde en masse (tableau key => value) */
    public function saveValues(array $keyValues): void;
}
