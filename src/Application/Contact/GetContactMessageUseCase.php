<?php

declare(strict_types=1);

namespace Rore\Application\Contact;

use Rore\Domain\Contact\Entity\ContactMessage;
use Rore\Domain\Contact\Repository\ContactMessageRepositoryInterface;
use RuntimeException;
use Rore\Infrastructure\Persistence\MySqlContactMessageRepository;
use Rore\Framework\Di\BindAdapter;

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
