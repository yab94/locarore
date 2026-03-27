<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Port\ContactMessageRepositoryInterface;
use Rore\Adapter\MySqlContactMessageRepository;
use RRB\Di\BindAdapter;

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
