<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Port\SettingsRepositoryInterface;
use Rore\Adapter\MySqlSettingsRepository;
use RRB\Di\BindAdapter;

class SaveSettingsUseCase
{
    public function __construct(
        #[BindAdapter(MySqlSettingsRepository::class)]
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
