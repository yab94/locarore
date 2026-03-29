<?php

declare(strict_types=1);

namespace Rore\Application\Auth\UseCase;

use RRB\Session\SessionInterface;

class LogoutAdminUseCase
{
    public function __construct(
        private readonly SessionInterface $session,
        private readonly string $sessionKey,
    ) {
    }

    public function execute(): void
    {
        $this->session->remove($this->sessionKey);
    }
}
