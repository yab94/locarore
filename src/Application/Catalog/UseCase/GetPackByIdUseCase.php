<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\UseCase;

use Rore\Domain\Catalog\Entity\Pack;
use Rore\Application\Catalog\Port\PackRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlPackRepository;
use RRB\Di\BindAdapter;

/**
 * Récupère un pack par son ID.
 */
final class GetPackByIdUseCase
{
    public function __construct(
        #[BindAdapter(MySqlPackRepository::class)]
        private readonly PackRepositoryInterface $packRepo,
    ) {}

    public function execute(int $id): ?Pack
    {
        return $this->packRepo->findById($id);
    }
}
