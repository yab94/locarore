<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Config;

use Rore\Application\Config\ConfigInterface;
use Rore\Support\Castable;
use Rore\Support\Typable;

final class Config extends Typable implements ConfigInterface
{
    use Castable;

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
     * NOTE: array_walk_recursive ne modifie pas les CLÉS d'arrays associatifs,
     * donc on doit parcourir manuellement pour résoudre les clés de routes.
     */
    private static function resolveConfigReferences(array $data): array
    {
        $maxIterations = 10;
        
        for ($iteration = 0; $iteration < $maxIterations; $iteration++) {
            $hasChanges = false;
            $data = self::resolveArrayRecursive($data, $data, $hasChanges);
            
            if (!$hasChanges) {
                break;
            }
        }
        
        return $data;
    }
    
    /**
     * Résout récursivement les références dans valeurs ET clés.
     */
    private static function resolveArrayRecursive(array $array, array $fullData, bool &$hasChanges): array
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            // Résoudre la clé
            $resolvedKey = self::resolveString($key, $fullData, $hasChanges);
            
            // Résoudre la valeur
            if (is_array($value)) {
                $resolvedValue = self::resolveArrayRecursive($value, $fullData, $hasChanges);
            } elseif (is_string($value)) {
                $resolvedValue = self::resolveString($value, $fullData, $hasChanges);
            } else {
                $resolvedValue = $value;
            }
            
            $result[$resolvedKey] = $resolvedValue;
        }
        
        return $result;
    }
    
    /**
     * Résout les références @{section.key} dans une string.
     */
    private static function resolveString(string $value, array $data, bool &$hasChanges): string
    {
        $original = $value;
        
        $resolved = preg_replace_callback(
            '/@\{([^}]+)\}/',
            function($m) use ($data) {
                $path = $m[1];
                $parts = explode('.', $path);
                
                if (count($parts) === 2 && isset($data[$parts[0]][$parts[1]])) {
                    return (string) $data[$parts[0]][$parts[1]];
                }
                
                return $m[0];
            },
            $value
        );
        
        if ($resolved !== $original) {
            $hasChanges = true;
        }
        
        return $resolved;
    }

    public function isProduction(): bool
    {
        return getenv('APP_ENV') !== 'dev';
    }

    public function get(string $path, mixed $default = null): mixed
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
