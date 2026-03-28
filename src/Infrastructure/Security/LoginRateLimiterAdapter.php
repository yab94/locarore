<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Security;

use Rore\Application\Auth\Port\AdminLoginRateLimiterInterface;
use RRB\Di\BindAdapter;
use RRB\Di\BindConfig;
use RRB\Security\RateLimiter;
use RRB\Session\PhpSession;
use RRB\Session\SessionInterface;

final class LoginRateLimiterAdapter extends RateLimiter implements AdminLoginRateLimiterInterface
{
    public function __construct(
        #[BindAdapter(PhpSession::class)]
        SessionInterface $session,
        #[BindConfig('admin.login_attempts')]
        int $maxAttempts,
        #[BindConfig('admin.lockout_seconds')]
        int $lockoutSeconds,
    ) {
        parent::__construct($session, 'admin_login', $maxAttempts, $lockoutSeconds);
    }
}
