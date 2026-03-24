<?php

declare(strict_types=1);

namespace Rore\Application\Security;

/**
 * Port applicatif pour la gestion CSRF.
 */
interface CsrfTokenManagerInterface
{
    public function token(): string;
    public function validate(string $postedToken): bool;
    public function postKey(): string;
}
