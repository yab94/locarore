<?php

declare(strict_types=1);

namespace Rore\Application\Contact;

use Rore\Domain\Contact\Repository\ContactMessageRepositoryInterface;

final class DeleteContactMessageUseCase
{
    public function __construct(
        private readonly ContactMessageRepositoryInterface $repo,
    ) {}

    public function execute(int $id): void
    {
        $this->repo->delete($id);
    }
}
