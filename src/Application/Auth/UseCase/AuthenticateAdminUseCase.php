<?php

declare(strict_types=1);

namespace Rore\Application\Auth\UseCase;

use Rore\Application\Auth\Port\AdminLoginRateLimiterInterface;
use Rore\Infrastructure\Security\LoginRateLimiterAdapter;
use RRB\Di\BindAdapter;
use RRB\Di\BindConfig;
use RRB\Session\PhpSession;
use RRB\Session\SessionInterface;

class AuthenticateAdminUseCase
{
    public function __construct(
        #[BindAdapter(LoginRateLimiterAdapter::class)]
        private readonly AdminLoginRateLimiterInterface $rateLimiter,
        #[BindAdapter(PhpSession::class)]
        private readonly SessionInterface $session,
        #[BindConfig('admin.password')]
        private readonly string $adminPassword,
        #[BindConfig('admin.session_key')]
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
