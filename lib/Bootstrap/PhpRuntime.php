<?php

declare(strict_types=1);

namespace RRB\Bootstrap;

/**
 * Applique les directives ini_set définies dans la section [php] de
 * l'ini chargé (default.ini mergé avec {env}.ini).
 */
final class PhpRuntime
{
    static public function apply(array $settings): void
    {
        foreach ($settings as $directive => $value) {
            if ($directive === 'include_path') {
                set_include_path($value . PATH_SEPARATOR . get_include_path());
            } else {
                ini_set((string) $directive, (string) $value);
            }
        }
    }
}
