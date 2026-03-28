<?php

declare(strict_types=1);

namespace Rore\Application\Auth\UseCase;

use RRB\Di\BindAdapter;
use RRB\Session\PhpSession;
use RRB\Session\SessionInterface;

class LogoutAdminUseCase
{
    public function __construct(
        #[BindAdapter(PhpSession::class)]
        private readonly SessionInterface $session,
    ) {
    }

    public function execute(): void
    {
        $this->session->remove('admin_logged_in');
    }
}
