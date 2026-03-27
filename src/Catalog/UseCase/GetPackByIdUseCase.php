<?php

declare(strict_types=1);

namespace Rore\Catalog\UseCase;

use Rore\Catalog\Entity\Pack;
use Rore\Catalog\Port\PackRepositoryInterface;
use Rore\Catalog\Adapter\MySqlPackRepository;
use Rore\Framework\Di\BindAdapter;

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
