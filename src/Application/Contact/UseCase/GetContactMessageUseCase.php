<?php

declare(strict_types=1);

namespace Rore\Application\Contact\UseCase;

use Rore\Domain\Contact\Entity\ContactMessage;
use Rore\Application\Contact\Port\ContactMessageRepositoryInterface;
use RuntimeException;

final class GetContactMessageUseCase
{
    public function __construct(
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
