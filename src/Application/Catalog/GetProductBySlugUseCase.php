<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Entity\Product;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;

/**
 * Récupère un produit par son slug.
 */
final class GetProductBySlugUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepo,
    ) {}

    public function execute(string $slug): ?Product
    {
        return $this->productRepo->findBySlug($slug);
    }
}
