<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Config;

/**
 * Applique les directives ini_set définies dans la section [php] de app.ini
 * pour l'environnement courant (app.env = dev|prod).
 * Tout env inconnu est traité comme "prod" (sécurité par défaut).
 */
final class PhpRuntime
{
    public function __construct(private readonly Config $config) {}

    public function boot(): void
    {
        $rawEnv   = $this->config->getStringParam('app.env', 'prod');
        $env      = ($rawEnv === 'dev') ? 'dev' : 'prod';
        $settings = $this->config->getParam("php.{$env}");

        if (!is_array($settings)) {
            return;
        }

        foreach ($settings as $directive => $value) {
            ini_set((string) $directive, (string) $value);
        }
    }
}
