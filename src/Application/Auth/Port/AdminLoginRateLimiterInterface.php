<?php

declare(strict_types=1);

namespace Rore\Application\Auth\Port;

interface AdminLoginRateLimiterInterface
{
    public function isLocked(): bool;
    public function secondsUntilUnlock(): int;
    public function hit(): void;
    public function reset(): void;
}
