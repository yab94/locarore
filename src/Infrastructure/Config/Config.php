<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Config;

use Rore\Application\Config\ConfigInterface;
use Rore\Support\Castable;
use Rore\Support\Typable;

final class Config extends Typable implements ConfigInterface
{
    use Castable;

    public function parseIni(string $file): void
    {
        $raw = file_get_contents($file);
        $raw = preg_replace_callback(
            '/\$\{([^}]+)\}/',
            fn($m) => getenv($m[1]) ?: $m[0],
            $raw,
        );

        $this->data = $this->resolveConfigReferences($this->deepMerge($this->data, parse_ini_string($raw, true) ?: []));

    }

    /**
     * Fusionne $override dans $base récursivement (section par section).
     */
    private function deepMerge(array $base, array $override): array
    {
        foreach ($override as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                $base[$key] = $this->deepMerge($base[$key], $value);
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
    private function resolveConfigReferences(array $data): array
    {
        $maxIterations = 10;
        
        for ($iteration = 0; $iteration < $maxIterations; $iteration++) {
            $hasChanges = false;
            $data = $this->resolveArrayRecursive($data, $data, $hasChanges);
            
            if (!$hasChanges) {
                break;
            }
        }
        
        return $data;
    }
    
    /**
     * Résout récursivement les références dans valeurs ET clés.
     */
    private function resolveArrayRecursive(array $array, array $fullData, bool &$hasChanges): array
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            // Résoudre la clé
            $resolvedKey = $this->resolveString($key, $fullData, $hasChanges);
            
            // Résoudre la valeur
            if (is_array($value)) {
                $resolvedValue = $this->resolveArrayRecursive($value, $fullData, $hasChanges);
            } elseif (is_string($value)) {
                $resolvedValue = $this->resolveString($value, $fullData, $hasChanges);
            } else {
                $resolvedValue = $value;
            }
            
            $result[$resolvedKey] = $resolvedValue;
        }
        
        return $result;
    }
    
    private function resolveString(string $value, array $data, bool &$hasChanges): string
    {
        $original = $value;
        
        $resolved = preg_replace_callback(
            '/@\{([^}]+)\}/',
            function($m) use ($data) {
                $path = $m[1];
                $parts = explode('.', $path);
                
                // Si c'est une référence simple (BASE_PATH), chercher dans les données de premier niveau
                if (count($parts) === 1 && isset($data[$parts[0]])) {
                    return (string) $data[$parts[0]];
                }
                
                // Sinon, chercher dans section.key
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
