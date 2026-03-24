<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Repository\PackRepositoryInterface;

/**
 * Récupère tous les packs du catalogue.
 */
final class GetAllPacksUseCase
{
    public function __construct(
        private readonly PackRepositoryInterface $packRepo,
    ) {}

    /**
     * @return array<int, \Rore\Domain\Catalog\Entity\Pack>
     */
    public function execute(): array
    {
        return $this->packRepo->findAll();
    }
}
