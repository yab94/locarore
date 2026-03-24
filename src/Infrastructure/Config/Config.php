<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Config;

use Rore\Infrastructure\Shared\AbstractTypedParams;

final class Config extends AbstractTypedParams
{
    public function __construct(private readonly array $data)
    {
    }

    /**
     * Charge .env puis config/app.ini depuis la racine du projet.
     *
     * @param string $basePath  chemin absolu vers la racine (BASE_PATH)
     */
    public static function load(string $basePath): self
    {
        // 1. Charger .env
        $envFile = $basePath . '/.env';
        if (file_exists($envFile)) {
            foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$k, $v] = explode('=', $line, 2);
                $_ENV[trim($k)] = trim($v);
                putenv(trim($k) . '=' . trim($v));
            }
        }

        // 2. Charger app.ini en résolvant les placeholders ${VAR}
        $iniRaw = file_get_contents($basePath . '/config/app.ini');
        $iniRaw = preg_replace_callback(
            '/\$\{([^}]+)\}/',
            fn($m) => getenv($m[1]) ?: $m[0],
            $iniRaw,
        );

        return new self(parse_ini_string($iniRaw, true));
    }

    public function getParam(string $path, mixed $default = null): mixed
    {
        $path = trim($path);
        if ($path === '') {
            return $default;
        }

        $cursor = $this->data;
        foreach (explode('.', $path) as $segment) {
            if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
                return $default;
            }
            $cursor = $cursor[$segment];
        }

        return $cursor;
    }
}
