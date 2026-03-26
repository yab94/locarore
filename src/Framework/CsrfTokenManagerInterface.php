<?php

declare(strict_types=1);

namespace Rore\Framework;

interface CsrfTokenManagerInterface
{
    public function token(): string;
    public function validate(string $postedToken): bool;
    public function postKey(): string;
}
