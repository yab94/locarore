<?php

declare(strict_types=1);

namespace Rore\Framework;

use Rore\Framework\Castable;

/**
 * Encodeur HTML injectable dans les templates.
 * Utilisation : $html($value)  ou  $html->encode($value)
 */
final class HtmlHelper
{
    use Castable;

    /**
     * Échappe une valeur pour un affichage sûr en HTML.
     */
    public function encode(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Raccourci : $html($value)
     */
    public function __invoke(mixed $value): string
    {
        return $this->encode($value);
    }
}
