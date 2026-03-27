<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\UseCase;

use Rore\Domain\Catalog\Repository\PackRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlPackRepository;
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
