<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\UseCase;

use Rore\Application\Catalog\Port\PackRepositoryInterface;

class TogglePackUseCase
{
    public function __construct(
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
