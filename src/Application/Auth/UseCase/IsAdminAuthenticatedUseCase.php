<?php

declare(strict_types=1);

namespace Rore\Application\Auth\UseCase;

use RRB\Di\BindAdapter;
use RRB\Di\BindConfig;
use RRB\Session\PhpSession;
use RRB\Session\SessionInterface;

class IsAdminAuthenticatedUseCase
{
    public function __construct(
        #[BindAdapter(PhpSession::class)]
        private readonly SessionInterface $session,
        #[BindConfig('admin.session_key')]
        private readonly string $sessionKey,
    ) {
    }

    public function execute(): bool
    {
        return !empty($this->session->get($this->sessionKey));
    }
}
