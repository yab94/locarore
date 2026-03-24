<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Config;

/**
 * Applique les directives ini_set définies dans la section [php] de
 * l'ini chargé (default.ini mergé avec {env}.ini).
 */
final class PhpRuntime
{
    public function __construct(private readonly Config $config) {}

    public function boot(): void
    {
        $settings = $this->config->getParam('php');

        if (!is_array($settings)) {
            return;
        }

        foreach ($settings as $directive => $value) {
            ini_set((string) $directive, (string) $value);
        }
    }
}
