<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Port\CategoryRepositoryInterface;
use Rore\Port\PackRepositoryInterface;
use Rore\Port\ProductRepositoryInterface;
use Rore\Adapter\MySqlCategoryRepository;
use Rore\Adapter\MySqlProductRepository;
use Rore\Adapter\MySqlPackRepository;
use RRB\Di\BindAdapter;

/**
 * Récupère une catégorie avec ses produits et packs.
 */
final class GetCategoryWithItemsUseCase
{
    public function __construct(
        #[BindAdapter(MySqlCategoryRepository::class)]
        private readonly CategoryRepositoryInterface $categoryRepo,
        #[BindAdapter(MySqlProductRepository::class)]
        private readonly ProductRepositoryInterface $productRepo,
        #[BindAdapter(MySqlPackRepository::class)]
        private readonly PackRepositoryInterface $packRepo,
    ) {}

    /**
     * @return array{category: ?\Rore\Entity\Category, products: array, packs: array, productsById: array}|null
     */
    public function execute(string $path): ?array
    {
        // Extraire le slug du dernier segment
        $segments = explode('/', trim($path, '/'));
        $slug     = end($segments);

        $category = $this->categoryRepo->findBySlug($slug);
        
        if ($category === null) {
            return null;
        }

        $products = $this->productRepo->findActiveByCategorySlug($slug);
        $packs    = $this->packRepo->findActiveByCategorySlug($slug);

        // Produits indexés par id pour les pack-cards
        $allProducts  = $this->productRepo->findAll();
        $productsById = [];
        foreach ($allProducts as $p) {
            $productsById[$p->getId()] = $p;
        }

        return [
            'category'     => $category,
            'products'     => $products,
            'packs'        => $packs,
            'productsById' => $productsById,
        ];
    }
}
