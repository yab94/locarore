<?php

declare(strict_types=1);

namespace Rore\Application\Settings;

use Rore\Domain\Settings\Repository\SettingsRepositoryInterface;

final class GetSettingUseCase
{
    public function __construct(
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
