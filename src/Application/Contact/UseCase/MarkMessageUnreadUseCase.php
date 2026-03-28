<?php

declare(strict_types=1);

namespace Rore\Application\Contact\UseCase;

use Rore\Application\Contact\Port\ContactMessageRepositoryInterface;
use RuntimeException;
use Rore\Infrastructure\Persistence\MySqlContactMessageRepository;
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
