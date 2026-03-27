<?php

declare(strict_types=1);

namespace Rore\Shared\Security;

use Rore\Framework\Bootstrap\Config;
use Rore\Framework\Di\BindAdapter;
use Rore\Framework\Di\BindConfig;
use Rore\Framework\Security\RateLimiter;
use Rore\Framework\Session\PhpSession;
use Rore\Framework\Session\SessionInterface;

final class LoginRateLimiter extends RateLimiter
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

