<?php

declare(strict_types=1);

namespace Rore\Framework\Type;

use InvalidArgumentException;

/**
 * Fournit une méthode statique cast() pour valider et typer un objet.
 *
 * Usage dans un template (après extract) :
 *   $product = Product::cast($product);  // IDE : Product ✓, runtime check ✓
 */
trait Castable
{
    public static function castOrNull(mixed $value): ?static
    {
        return $value === null ? null : static::cast($value);        
    }

    public static function cast(mixed $value): static
    {
        if (!$value instanceof static) {
            throw new InvalidArgumentException(sprintf(
                'Expected %s, got %s.',
                static::class,
                get_debug_type($value)
            ));
        }

        return $value;
    }
}
