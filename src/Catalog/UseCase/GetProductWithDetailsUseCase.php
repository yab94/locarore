<?php

declare(strict_types=1);

namespace Rore\Catalog\UseCase;

use Rore\Catalog\Entity\Product;
use Rore\Catalog\Port\CategoryRepositoryInterface;
use Rore\Catalog\Port\ProductRepositoryInterface;
use Rore\Catalog\Adapter\MySqlCategoryRepository;
use Rore\Catalog\Adapter\MySqlProductRepository;
use Rore\Framework\Di\BindAdapter;

/**
 * Récupère un produit avec toutes ses données liées (catégorie, etc).
 */
final class GetProductWithDetailsUseCase
{
    public function __construct(
        #[BindAdapter(MySqlProductRepository::class)]
        private readonly ProductRepositoryInterface $productRepo,
        #[BindAdapter(MySqlCategoryRepository::class)]
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
