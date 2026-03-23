<?php

declare(strict_types=1);

/**
 * Helpers globaux — namespace Rore
 */

/**
 * Formate une date en français.
 */
function formatDate(string|\DateTimeInterface $date, string $format = 'd/m/Y'): string
{
    if (is_string($date)) {
        $date = new \DateTimeImmutable($date);
    }
    return $date->format($format);
}

/**
 * Retourne le libellé d'un intervalle de dates.
 * Ex : "du 12 juin au 14 juin 2026"
 */
function dateRangeLabel(string|\DateTimeInterface $start, string|\DateTimeInterface $end): string
{
    if (is_string($start)) $start = new \DateTimeImmutable($start);
    if (is_string($end))   $end   = new \DateTimeImmutable($end);

    $months = [
        1 => 'janvier', 2 => 'février',  3 => 'mars',      4 => 'avril',
        5 => 'mai',     6 => 'juin',      7 => 'juillet',   8 => 'août',
        9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
    ];

    $startStr = $start->format('j') . ' ' . $months[(int)$start->format('n')];
    $endStr   = $end->format('j')   . ' ' . $months[(int)$end->format('n')]
              . ' ' . $end->format('Y');

    if ($start->format('Y') !== $end->format('Y')) {
        $startStr .= ' ' . $start->format('Y');
    }

    return "du $startStr au $endStr";
}

/**
 * Calcul du nombre de jours dans un intervalle (inclusif).
 */
function nbDays(string|\DateTimeInterface $start, string|\DateTimeInterface $end): int
{
    if (is_string($start)) $start = new \DateTimeImmutable($start);
    if (is_string($end))   $end   = new \DateTimeImmutable($end);

    return (int) $start->diff($end)->days + 1;
}

/**
 * Echappe une valeur pour l'affichage HTML.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Retourne le libellé d'un statut de réservation.
 */
function statusLabel(string $status): string
{
    return match($status) {
        'pending'   => 'En attente',
        'quoted'    => 'Devis envoyé',
        'confirmed' => 'Confirmée',
        'cancelled' => 'Annulée',
        default     => $status,
    };
}

/**
 * Retourne les classes Tailwind pour le badge d'un statut.
 */
function statusBadgeClass(string $status): string
{
    return match($status) {
        'pending'   => 'bg-yellow-100 text-yellow-800',
        'quoted'    => 'bg-orange-100 text-orange-800',
        'confirmed' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800',
        default     => 'bg-gray-100 text-gray-800',
    };
}

/**
 * Retourne la valeur d'une clé de paramètre CMS.
 * Supporte l'interpolation de variables : setting('hero.title', ['name' => 'Locarore'])
 * remplace {name} dans la valeur stockée.
 */
function setting(string $key, array $vars = []): string
{
    static $repo = null;
    if ($repo === null) {
        $repo = new \Rore\Infrastructure\Persistence\MySqlSettingsRepository();
    }
    $setting = $repo->findByKey($key);
    $value   = $setting?->getValue() ?? '';

    if ($vars) {
        foreach ($vars as $k => $v) {
            $value = str_replace('{' . $k . '}', (string) $v, $value);
        }
    }
    return $value;
}

/**
 * Comme setting() mais retourne la valeur échappée pour l'affichage HTML.
 */
function se(string $key, array $vars = []): string
{
    return e(setting($key, $vars));
}

/**
 * Génère ou récupère le token CSRF de la session.
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Retourne un champ hidden contenant le token CSRF.
 */
function csrfField(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrfToken()) . '">';
}

