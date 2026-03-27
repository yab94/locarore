<?php

declare(strict_types=1);

namespace Rore\Contact\UseCase;

use Rore\Contact\Port\ContactMessageRepositoryInterface;
use RuntimeException;
use Rore\Contact\Adapter\MySqlContactMessageRepository;
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
