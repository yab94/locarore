<?php

declare(strict_types=1);

namespace Rore\Application\Auth\UseCase;

use RRB\Session\SessionInterface;

class IsAdminAuthenticatedUseCase
{
    public function __construct(
        private readonly SessionInterface $session,
        private readonly string $sessionKey,
    ) {
    }

    public function execute(): bool
    {
        return !empty($this->session->get($this->sessionKey));
    }
}
