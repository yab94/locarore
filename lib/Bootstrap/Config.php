<?php

declare(strict_types=1);

namespace RRB\Bootstrap;

use RRB\Type\Castable;
use RRB\Type\Typable;

final class Config extends Typable
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
     *
     * Les passes sont répétées jusqu'à stabilisation (aucune substitution restante).
     * Une référence non résolue après une passe complète sans changement indique
     * soit une clé inexistante soit un cycle — une exception est levée dans ce cas.
     */
    private function resolveConfigReferences(array $data): array
    {
        while (true) {
            $hasChanges = false;
            $data = $this->resolveArrayRecursive($data, $data, $hasChanges);

            if (!$hasChanges) {
                break;
            }

            // Vérifie qu'il ne reste aucune référence non résolue après cette passe
            $remaining = [];
            array_walk_recursive($data, function ($value) use (&$remaining) {
                if (is_string($value) && preg_match('/@\{[^}]+\}/', $value)) {
                    $remaining[] = $value;
                }
            });

            if ($remaining !== [] && !$hasChanges) {
                throw new \RuntimeException(
                    'Config : références circulaires ou inexistantes détectées : '
                    . implode(', ', array_unique($remaining))
                );
            }
        }

        // Vérification finale : aucune @{} ne doit subsister
        $unresolved = [];
        array_walk_recursive($data, function ($value) use (&$unresolved) {
            if (is_string($value) && preg_match_all('/@\{([^}]+)\}/', $value, $m)) {
                array_push($unresolved, ...$m[0]);
            }
        });

        if ($unresolved !== []) {
            throw new \RuntimeException(
                'Config : références non résolues (clé inexistante ou cycle) : '
                . implode(', ', array_unique($unresolved))
            );
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