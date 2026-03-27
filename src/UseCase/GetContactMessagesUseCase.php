<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Port\ContactMessageRepositoryInterface;
use Rore\Adapter\MySqlContactMessageRepository;
use RRB\Di\BindAdapter;

final class GetContactMessagesUseCase
{
    public function __construct(
        #[BindAdapter(MySqlContactMessageRepository::class)]
        private readonly ContactMessageRepositoryInterface $repo,
    ) {}

    public function all(): array
    {
        return $this->repo->findAll();
    }

    public function unread(): array
    {
        return $this->repo->findUnread();
    }

    public function countUnread(): int
    {
        return $this->repo->countUnread();
    }
}
