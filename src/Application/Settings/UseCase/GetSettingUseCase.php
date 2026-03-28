<?php

declare(strict_types=1);

namespace Rore\Application\Settings\UseCase;

use Rore\Application\Settings\Port\SettingsRepositoryInterface;
use RRB\Type\Castable;
use Rore\Infrastructure\Persistence\MySqlSettingsRepository;
use RRB\Di\BindAdapter;

final class GetSettingUseCase
{
    use Castable;
    public function __construct(
        #[BindAdapter(MySqlSettingsRepository::class)]
        private readonly SettingsRepositoryInterface $repo,
    ) {}

    /**
     * @param array<string,string> $vars
     */
    public function get(string $key, array $vars = []): string
    {
        return $this->repo->findByKey($key)?->resolve($vars) ?? '';
    }
}
