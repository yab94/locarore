<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Entity\ContactMessage;
use Rore\Port\ContactMessageRepositoryInterface;
use RuntimeException;
use Rore\Adapter\MySqlContactMessageRepository;
use RRB\Di\BindAdapter;

final class GetContactMessageUseCase
{
    public function __construct(
        #[BindAdapter(MySqlContactMessageRepository::class)]
        private readonly ContactMessageRepositoryInterface $repo,
    ) {}

    public function execute(int $id): ContactMessage
    {
        $message = $this->repo->findById($id);
        if ($message === null) {
            throw new RuntimeException("Message #{$id} introuvable.");
        }
        return $message;
    }
}
