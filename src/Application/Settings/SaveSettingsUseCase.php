<?php

declare(strict_types=1);

namespace Rore\Application\Settings;

use Rore\Domain\Settings\Repository\SettingsRepositoryInterface;

class SaveSettingsUseCase
{
    public function __construct(
        private SettingsRepositoryInterface $settingsRepository,
    ) {}

    /**
     * @param array<string, string> $keyValues
     */
    public function execute(array $keyValues): void
    {
        if (empty($keyValues)) {
            return;
        }

        $this->settingsRepository->saveValues($keyValues);
    }
}
