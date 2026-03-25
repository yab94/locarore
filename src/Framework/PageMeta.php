<?php

declare(strict_types=1);

namespace Rore\Framework;

use Rore\Framework\Castable;

/**
 * Value Object avec property hooks pour les métadonnées SEO.
 */
final class PageMeta
{
    use Castable;

    private array $titleParts = [];
    private array $descParts = [];
    private array $keywordParts = [];

    public string $title {
        get => $this->formatTitle($this->titleParts);
        set(string|array $value) {
            $this->titleParts = is_array($value) ? $value : [$value];
        }
    }

    public string $description {
        get => $this->formatDescription($this->descParts);
        set(string|array $value) {
            $this->descParts = is_array($value) ? $value : [$value];
        }
    }

    public string $keywords {
        get => $this->formatKeywords($this->keywordParts);
        set(string|array $value) {
            $this->keywordParts = is_array($value) ? $value : explode(',', $value);
        }
    }

    public function __construct(
        public string  $robots       = 'index, follow',
        public string  $canonicalUrl = '',
        public string  $ogImage      = '',
        public int     $ogImageWidth  = 0,
        public int     $ogImageHeight = 0,
        public string  $ogType        = 'website',
        string|array   $title        = '',
        string|array   $description  = '',
        string|array   $keywords     = '',
    ) {
        if (!empty($title)) {
            $this->title = $title;
        }
        if (!empty($description)) {
            $this->description = $description;
        }
        if (!empty($keywords)) {
            $this->keywords = $keywords;
        }
    }

    /**
     * Construit le titre de page SEO avec logique smart ≤ 60 caractères.
     * Format : "Nom — Parent1 — Parent2 — Site"
     */
    private function formatTitle(array $parts): string
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

    /**
     * Génère une meta description propre à partir de textes bruts ou HTML.
     * Supprime les balises, concatène avec " — ", tronque à 160 caractères.
     */
    private function formatDescription(array $parts): string
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
     */
    private function formatKeywords(array $words): string
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
}
