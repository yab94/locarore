<?php

declare(strict_types=1);

namespace Rore\Catalog\UseCase;

use Rore\Catalog\Port\PackRepositoryInterface;
use Rore\Catalog\Adapter\MySqlPackRepository;
use Rore\Framework\Di\BindAdapter;

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
