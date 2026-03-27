<?php

declare(strict_types=1);

namespace Rore\Settings\UseCase;

use Rore\Settings\Port\SettingsRepositoryInterface;
use Rore\Framework\Type\Castable;
use Rore\Settings\Adapter\MySqlSettingsRepository;
use Rore\Framework\Di\BindAdapter;

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
