<?php

declare(strict_types=1);

namespace Rore\Application\Settings;

use Rore\Domain\Settings\Repository\SettingsRepositoryInterface;

/**
 * Récupère tous les paramètres de configuration.
 */
final class GetAllSettingsUseCase
{
    public function __construct(
        private readonly SettingsRepositoryInterface $settingsRepo,
    ) {}

    /**
     * @return array<string, \Rore\Domain\Settings\Entity\Setting>
     */
    public function execute(): array
    {
        return $this->settingsRepo->findAll();
    }
}
