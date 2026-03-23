<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Config;

use Rore\Infrastructure\Database\Connection;

/**
 * Initialisation commune à tous les entrypoints (web, CLI…).
 *
 * Doit être appelé après que BASE_PATH est défini et que l'autoloader
 * est enregistré.
 *
 * @return array<string, mixed> La config complète issue de app.ini
 */
final class Bootstrap
{
    /**
     * 1. Charge .env dans $_ENV / putenv()
     * 2. Charge config/app.ini en résolvant les placeholders ${VAR}
     * 3. Initialise la connexion à la base de données
     *
     * @return array<string, mixed>
     */
    public static function boot(): array
    {
        self::loadEnv();
        $config = self::loadIni();
        Connection::init($config['database']);

        return $config;
    }

    // ─────────────────────────────────────────────────────────────────────

    private static function loadEnv(): void
    {
        $envFile = BASE_PATH . '/.env';
        if (!file_exists($envFile)) {
            return;
        }

        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            $_ENV[trim($k)] = trim($v);
            putenv(trim($k) . '=' . trim($v));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private static function loadIni(): array
    {
        $iniRaw = file_get_contents(BASE_PATH . '/config/app.ini');
        $iniRaw = preg_replace_callback(
            '/\$\{([^}]+)\}/',
            fn($m) => getenv($m[1]) ?: $m[0],
            $iniRaw,
        );

        return parse_ini_string($iniRaw, true);
    }
}
