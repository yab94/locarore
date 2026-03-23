<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Security;

/**
 * Gestion du token CSRF basé sur la session.
 *
 * - token()    : génère ou récupère le token de la session courante
 * - validate() : vérifie la correspondance session ↔ POST (timing-safe)
 * - field()    : rendu HTML du champ hidden pour les formulaires
 */
final class CsrfTokenManager
{
    private const SESSION_KEY = 'csrf_token';
    private const POST_KEY    = '_csrf';

    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    public static function validate(): bool
    {
        return hash_equals(
            $_SESSION[self::SESSION_KEY] ?? '',
            $_POST[self::POST_KEY]        ?? '',
        );
    }

    public static function field(): string
    {
        return '<input type="hidden" name="' . self::POST_KEY . '" value="'
            . htmlspecialchars(self::token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '">';
    }
}
