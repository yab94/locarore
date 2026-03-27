<?php

declare(strict_types=1);

namespace Rore\Settings\UseCase;

use Rore\Settings\Port\SettingsRepositoryInterface;
use Rore\Settings\Adapter\MySqlSettingsRepository;
use Rore\Framework\Di\BindAdapter;

/**
 * Récupère tous les paramètres de configuration.
 */
final class GetAllSettingsUseCase
{
    public function __construct(
        #[BindAdapter(MySqlSettingsRepository::class)]
        private readonly SettingsRepositoryInterface $settingsRepo,
    ) {}

    /**
     * @return array<string, \Rore\Settings\Entity\Setting>
     */
    public function execute(): array
    {
        return $this->settingsRepo->findAll();
    }
}
