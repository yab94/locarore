<?php

declare(strict_types=1);

namespace RRB\Type;

use InvalidArgumentException;

/**
 * Utilitaires de validation + cast pour les types scalaires.
 *
 * Valide le type à l'exécution ET retourne la valeur typée,
 * ce qui permet à l'IDE d'inférer le type statiquement.
 *
 * Usage dans un template (après extract) :
 *   $csrfToken = Cast::string($csrfToken);  // IDE : string ✓, runtime check ✓
 *   $items     = Cast::array($items);        // IDE : array  ✓, runtime check ✓
 */
final class Cast
{
    public static function string(mixed $value): string
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf(
                'Expected string, got %s.',
                get_debug_type($value)
            ));
        }

        return $value;
    }

    public static function int(mixed $value): int
    {
        if (!is_int($value)) {
            throw new InvalidArgumentException(sprintf(
                'Expected int, got %s.',
                get_debug_type($value)
            ));
        }

        return $value;
    }

    public static function float(mixed $value): float
    {
        if (!is_float($value) && !is_int($value)) {
            throw new InvalidArgumentException(sprintf(
                'Expected float, got %s.',
                get_debug_type($value)
            ));
        }

        return (float) $value;
    }

    public static function bool(mixed $value): bool
    {
        if (!is_bool($value)) {
            throw new InvalidArgumentException(sprintf(
                'Expected bool, got %s.',
                get_debug_type($value)
            ));
        }

        return $value;
    }

    public static function array(mixed $value): array
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException(sprintf(
                'Expected array, got %s.',
                get_debug_type($value)
            ));
        }

        return $value;
    }
}
