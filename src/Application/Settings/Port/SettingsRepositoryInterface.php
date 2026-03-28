<?php

declare(strict_types=1);

namespace Rore\Application\Settings\Port;

use Rore\Domain\Settings\Entity\Setting;

interface SettingsRepositoryInterface
{
    /** @return Setting[] indexés par clé */
    public function findAll(): array;

    public function findByKey(string $key): ?Setting;

    public function save(Setting $setting): void;

    /** Sauvegarde en masse (tableau key => value) */
    public function saveValues(array $keyValues): void;
}
