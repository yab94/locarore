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
     * Charge .env, puis config/default.ini, puis config/{env}.ini par-dessus
     * (deep merge section par section).
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

        // 2. Exposer BASE_PATH comme variable d'environnement
        //    afin que ${BASE_PATH} soit utilisable comme placeholder dans les ini
        putenv('BASE_PATH=' . $basePath);
        $_ENV['BASE_PATH'] = $basePath;

        // 3. Déterminer l'environnement (tout inconnu → prod, sécurité par défaut)
        $env = (getenv('APP_ENV') === 'dev') ? 'dev' : 'prod';

        // 3. Charger default.ini puis {env}.ini (deep merge)
        $data = self::parseIni($basePath . '/config/default.ini');
        $envIni = $basePath . '/config/' . $env . '.ini';
        if (file_exists($envIni)) {
            $data = self::deepMerge($data, self::parseIni($envIni));
        }

        // 4. Résoudre les références de config internes (${section.key})
        $data = self::resolveConfigReferences($data);

        return new self($data);
    }

    private static function parseIni(string $file): array
    {
        $raw = file_get_contents($file);
        $raw = preg_replace_callback(
            '/\$\{([^}]+)\}/',
            fn($m) => getenv($m[1]) ?: $m[0],
            $raw,
        );

        return parse_ini_string($raw, true) ?: [];
    }

    /**
     * Fusionne $override dans $base récursivement (section par section).
     */
    private static function deepMerge(array $base, array $override): array
    {
        foreach ($override as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                $base[$key] = self::deepMerge($base[$key], $value);
            } else {
                $base[$key] = $value;
            }
        }

        return $base;
    }

    /**
     * Résout les références de config internes @{section.key} dans toutes les valeurs.
     */
    private static function resolveConfigReferences(array $data): array
    {
        $maxIterations = 10; // Protection contre les références circulaires
        
        for ($i = 0; $i < $maxIterations; $i++) {
            $hasChanges = false;
            
            array_walk_recursive($data, function (&$value) use ($data, &$hasChanges) {
                if (!is_string($value)) {
                    return;
                }
                
                $original = $value;
                $value = preg_replace_callback(
                    '/@\{([^}]+)\}/',
                    function($m) use ($data) {
                        $path = $m[1];
                        $parts = explode('.', $path);
                        
                        // Tenter de résoudre section.key
                        if (count($parts) === 2 && isset($data[$parts[0]][$parts[1]])) {
                            return (string) $data[$parts[0]][$parts[1]];
                        }
                        
                        // Sinon laisser tel quel
                        return $m[0];
                    },
                    $value
                );
                
                if ($value !== $original) {
                    $hasChanges = true;
                }
            });
            
            if (!$hasChanges) {
                break;
            }
        }
        
        return $data;
    }

    public function isProduction(): bool
    {
        return getenv('APP_ENV') !== 'dev';
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
