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
    public function description(string ...$parts): string
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
    public function keywords(array $words): string
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
     * Construit le titre de page SEO avec logique smart ≤ 60 caractères.
     * Format : "Nom — Parent1 — Parent2 — Site"
     *
     * Règle : on ajoute les parties de gauche à droite tant que le total ≤ 60.
     * Si une partie entière ne rentre pas, on l'ignore (on ne tronque jamais).
     *
     * @param string[] $parts  du plus spécifique au plus général
     */
    public function title(string ...$parts): string
    {
        $parts = array_filter(array_map('trim', $parts), fn($p) => $p !== '');
        if (empty($parts)) {
            return '';
        }

        $result = [];
        $length = 0;

        foreach ($parts as $part) {
            $partLen = mb_strlen($part);
            $separator = empty($result) ? '' : ' — ';
            $separatorLen = mb_strlen($separator);

            // Si ajouter cette partie dépasse 60 chars, on s'arrête
            if ($length + $separatorLen + $partLen > 60) {
                break;
            }

            if (!empty($result)) {
                $length += $separatorLen;
            }
            $result[] = $part;
            $length += $partLen;
        }

        return implode(' — ', $result);
    }
}
