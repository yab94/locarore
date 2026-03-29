<?php

declare(strict_types=1);

namespace Rore\Application\Auth\UseCase;

use Rore\Application\Auth\Port\AdminLoginRateLimiterInterface;
use RRB\Session\SessionInterface;

class AuthenticateAdminUseCase
{
    public function __construct(
        private readonly AdminLoginRateLimiterInterface $rateLimiter,
        private readonly SessionInterface $session,
        private readonly string $adminPassword,
        private readonly string $sessionKey,
    ) {
    }

    /**
     * @return array{success: bool, error?: string, minutes?: int}
     */
    public function execute(string $password): array
    {
        if ($this->rateLimiter->isLocked()) {
            $minutes = (int) ceil($this->rateLimiter->secondsUntilUnlock() / 60);
            return [
                'success' => false,
                'error'   => "Trop de tentatives. Réessayez dans $minutes min.",
                'minutes' => $minutes,
            ];
        }

        if ($password === $this->adminPassword) {
            $this->rateLimiter->reset();
            $this->session->set($this->sessionKey, true);
            return ['success' => true];
        }

        $this->rateLimiter->hit();
        return [
            'success' => false,
            'error'   => 'Mot de passe incorrect.',
        ];
    }
}
