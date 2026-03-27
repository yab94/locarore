<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Port\ContactMessageRepositoryInterface;
use RuntimeException;
use Rore\Adapter\MySqlContactMessageRepository;
use RRB\Di\BindAdapter;

final class MarkMessageUnreadUseCase
{
    public function __construct(
        #[BindAdapter(MySqlContactMessageRepository::class)]
        private readonly ContactMessageRepositoryInterface $repo,
    ) {}

    public function execute(int $id): void
    {
        $message = $this->repo->findById($id);
        if ($message === null) {
            throw new RuntimeException("Message #{$id} introuvable.");
        }
        $message->markUnread();
        $this->repo->save($message);
    }
}
