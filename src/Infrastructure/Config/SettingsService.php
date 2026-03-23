<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Config;

use Rore\Application\Settings\SettingsServiceInterface;
use Rore\Domain\Settings\Repository\SettingsRepositoryInterface;

final class SettingsService implements SettingsServiceInterface
{
    public function __construct(
        private readonly SettingsRepositoryInterface $repo,
    ) {}

    /**
     * @param array<string,string> $vars
     */
    public function get(string $key, array $vars = []): string
    {
        $setting = $this->repo->findByKey($key);
        $value   = $setting?->getValue() ?? '';

        foreach ($vars as $k => $v) {
            $value = str_replace('{' . $k . '}', (string) $v, $value);
        }
        return $value;
    }
}
