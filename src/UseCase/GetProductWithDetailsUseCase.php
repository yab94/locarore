<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Entity\Product;
use Rore\Port\CategoryRepositoryInterface;
use Rore\Port\ProductRepositoryInterface;
use Rore\Adapter\MySqlCategoryRepository;
use Rore\Adapter\MySqlProductRepository;
use RRB\Di\BindAdapter;

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
