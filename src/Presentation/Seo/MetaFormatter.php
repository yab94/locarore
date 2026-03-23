<?php

declare(strict_types=1);

namespace Rore\Presentation\Seo;

/**
 * Utilitaires de formatage des métadonnées SEO.
 * Méthodes pures, sans état, sans dépendances externes.
 */
final class MetaFormatter
{
    /**
     * Génère une meta description propre à partir de textes bruts ou HTML.
     * Supprime les balises, concatène avec " — ", tronque à 160 caractères.
     */
    public static function description(string ...$parts): string
    {
        $text = '';
        foreach ($parts as $part) {
            $clean = trim(preg_replace('/\s+/', ' ', strip_tags($part)));
            if ($clean === '') continue;
            $text .= ($text !== '' ? ' — ' : '') . $clean;
        }
        if (mb_strlen($text) > 160) {
            $text = mb_substr($text, 0, 157) . '…';
        }
        return $text;
    }

    /**
     * Génère une liste de meta keywords dédupliquée (insensible à la casse).
     *
     * @param string[] $words
     */
    public static function keywords(array $words): string
    {
        $seen  = [];
        $clean = [];
        foreach ($words as $w) {
            $w = trim(strip_tags((string) $w));
            if ($w === '') continue;
            $lower = mb_strtolower($w);
            if (!isset($seen[$lower])) {
                $seen[$lower] = true;
                $clean[]      = $w;
            }
        }
        return implode(', ', $clean);
    }

    /**
     * Construit le titre de page SEO.
     * Format : "Nom — Parent1 — Parent2 — Site"
     *
     * @param string[] $parts  du plus spécifique au plus général
     */
    public static function title(string ...$parts): string
    {
        return implode(' — ', array_filter(array_map('trim', $parts), fn($p) => $p !== ''));
    }
}
