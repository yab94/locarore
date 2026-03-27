<?php

declare(strict_types=1);

namespace Rore\Application\Settings\UseCase;

use Rore\Application\Settings\Port\SettingsRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlSettingsRepository;
use Rore\Framework\Di\BindAdapter;

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
