<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Entity\Product;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;

/**
 * Récupère un produit par son ID.
 */
final class GetProductByIdUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepo,
    ) {}

    public function execute(int $id): ?Product
    {
        return $this->productRepo->findById($id);
    }
}
