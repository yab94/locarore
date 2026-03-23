<?php

declare(strict_types=1);

namespace Rore\Presentation\Template;

/**
 * Utilitaires d'échappement HTML pour les templates.
 */
final class Html
{
    /**
     * Échappe une valeur pour un affichage sûr en HTML.
     */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
