<?php

declare(strict_types=1);

namespace RRB\Di;

use Attribute;

/**
 * Raccourci DI : résout un paramètre à partir d'une clé de configuration.
 *
 * @example
 *   #[BindConfig('database.host')]
 *   string $host,
 *
 *   #[BindConfig('admin.login_attempts')]
 *   int $maxAttempts,
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class BindConfig
{
    public function __construct(public readonly string $key) {}
}
