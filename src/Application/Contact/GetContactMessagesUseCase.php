<?php

declare(strict_types=1);

namespace Rore\Application\Contact;

use Rore\Domain\Contact\Repository\ContactMessageRepositoryInterface;

final class GetContactMessagesUseCase
{
    public function __construct(
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
