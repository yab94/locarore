<?php

declare(strict_types=1);

namespace Rore\Presentation\Security;

use Rore\Framework\Bootstrap\Config;
use Rore\Framework\Di\Bind;
use Rore\Framework\Security\RateLimiter;
use Rore\Framework\Storage\PhpSessionStorage;

final class LoginRateLimiter extends RateLimiter
{
    public function __construct(
        PhpSessionStorage $session,
        #[Bind(static function (Config $c): int { return $c->getInt('admin.login_attempts'); })]
        int $maxAttempts,
        #[Bind(static function (Config $c): int { return $c->getInt('admin.lockout_seconds'); })]
        int $lockoutSeconds,
    ) {
        parent::__construct($session,  'admin_login', $maxAttempts, $lockoutSeconds);
    }
}

