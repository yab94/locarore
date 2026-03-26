<?php

declare(strict_types=1);

namespace Rore\Framework\Security;

use Rore\Framework\Session\SessionStorageInterface;

/**
 * Gestion du token CSRF basé sur la session.
 *
 * - token()    : génère ou récupère le token de la session courante
 * - validate() : vérifie la correspondance session ↔ token fourni (timing-safe)
 */
final class CsrfTokenManager implements CsrfTokenManagerInterface
{
    private const SESSION_KEY = 'csrf_token';
    private const POST_KEY    = '_csrf';

    public function __construct(
        private readonly SessionStorageInterface $session,
    ) {}

    public function token(): string
    {
        $current = $this->session->get(self::SESSION_KEY);
        if (!is_string($current) || $current === '') {
            $current = bin2hex(random_bytes(32));
            $this->session->set(self::SESSION_KEY, $current);
        }
        return $current;
    }

    public function validate(string $postedToken): bool
    {
        $sessionToken = $this->session->get(self::SESSION_KEY, '');

        if (!is_string($sessionToken) || $sessionToken === '' || $postedToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $postedToken);
    }

    public function postKey(): string
    {
        return self::POST_KEY;
    }
}
