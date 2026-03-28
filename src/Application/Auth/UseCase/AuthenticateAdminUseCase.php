<?php

declare(strict_types=1);

namespace Rore\Application\Auth\UseCase;

use Rore\Application\Auth\Port\AdminLoginRateLimiterInterface;
use Rore\Infrastructure\Security\LoginRateLimiterAdapter;
use RRB\Di\BindAdapter;
use RRB\Di\BindConfig;

class AuthenticateAdminUseCase
{
    public function __construct(
        #[BindAdapter(LoginRateLimiterAdapter::class)]
        private readonly AdminLoginRateLimiterInterface $rateLimiter,
        #[BindConfig('admin.password')]
        private readonly string $adminPassword,
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
            return ['success' => true];
        }

        $this->rateLimiter->hit();
        return [
            'success' => false,
            'error'   => 'Mot de passe incorrect.',
        ];
    }
}
