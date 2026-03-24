<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Entity\Pack;
use Rore\Domain\Catalog\Repository\PackRepositoryInterface;

/**
 * Récupère un pack par son ID.
 */
final class GetPackByIdUseCase
{
    public function __construct(
        private readonly PackRepositoryInterface $packRepo,
    ) {}

    public function execute(int $id): ?Pack
    {
        return $this->packRepo->findById($id);
    }
}
