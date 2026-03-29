<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Security;

use Rore\Application\Auth\Port\AdminLoginRateLimiterInterface;
use RRB\Security\RateLimiter;
use RRB\Session\SessionInterface;

final class LoginRateLimiterAdapter extends RateLimiter implements AdminLoginRateLimiterInterface
{
    public function __construct(
        SessionInterface $session,
        int $maxAttempts,
        int $lockoutSeconds,
    ) {
        parent::__construct($session, 'admin_login', $maxAttempts, $lockoutSeconds);
    }
}
