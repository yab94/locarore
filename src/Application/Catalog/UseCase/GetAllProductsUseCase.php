<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\UseCase;

use Rore\Application\Catalog\Port\ProductRepositoryInterface;

/**
 * Récupère tous les produits du catalogue.
 */
final class GetAllProductsUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepo,
    ) {}

    /**
     * @return array<int, \Rore\Domain\Catalog\Entity\Product>
     */
    public function execute(): array
    {
        return $this->productRepo->findAll();
    }
}
