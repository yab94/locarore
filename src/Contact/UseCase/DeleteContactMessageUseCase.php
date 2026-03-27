<?php

declare(strict_types=1);

namespace Rore\Contact\UseCase;

use Rore\Contact\Port\ContactMessageRepositoryInterface;
use Rore\Contact\Adapter\MySqlContactMessageRepository;
use Rore\Framework\Di\BindAdapter;

final class DeleteContactMessageUseCase
{
    public function __construct(
        #[BindAdapter(MySqlContactMessageRepository::class)]
        private readonly ContactMessageRepositoryInterface $repo,
    ) {}

    public function execute(int $id): void
    {
        $this->repo->delete($id);
    }
}
