<?php

declare(strict_types=1);

namespace Rore\Application\Settings\UseCase;

use Rore\Domain\Settings\Repository\SettingsRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlSettingsRepository;
use RRB\Di\BindAdapter;

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
     * @return array<string, \Rore\Domain\Settings\Entity\Setting>
     */
    public function execute(): array
    {
        return $this->settingsRepo->findAll();
    }
}
