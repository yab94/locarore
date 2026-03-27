<?php

declare(strict_types=1);

namespace Rore\Application\Contact\UseCase;

use Rore\Application\Contact\Port\ContactMessageRepositoryInterface;
use RuntimeException;
use Rore\Infrastructure\Persistence\MySqlContactMessageRepository;
use Rore\Framework\Di\BindAdapter;

final class MarkMessageReadUseCase
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
        $message->markRead();
        $this->repo->save($message);
    }
}
