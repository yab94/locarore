<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Port\PackRepositoryInterface;
use Rore\Adapter\MySqlPackRepository;
use RRB\Di\BindAdapter;

class TogglePackUseCase
{
    public function __construct(
        #[BindAdapter(MySqlPackRepository::class)]
        private PackRepositoryInterface $packRepository,
    ) {}

    public function execute(int $id): void
    {
        $pack = $this->packRepository->findById($id);
        if (!$pack) {
            throw new \RuntimeException("Pack introuvable.");
        }

        $pack->toggle();
        $pack->setUpdatedAt(new \DateTimeImmutable());
        $this->packRepository->save($pack);
    }
}
