<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\UseCase;

use Rore\Domain\Catalog\Entity\Product;
use Rore\Application\Catalog\Port\CategoryRepositoryInterface;
use Rore\Application\Catalog\Port\ProductRepositoryInterface;

/**
 * Récupère un produit avec toutes ses données liées (catégorie, etc).
 */
final class GetProductWithDetailsUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepo,
        private readonly CategoryRepositoryInterface $categoryRepo,
    ) {}

    /**
     * @return array{product: Product, allCategories: array}|null
     */
    public function execute(string $slug): ?array
    {
        $product = $this->productRepo->findBySlug($slug);
        
        if ($product === null) {
            return null;
        }

        return [
            'product'        => $product,
            'allCategories'  => $this->categoryRepo->findAllActive(),
        ];
    }
}
