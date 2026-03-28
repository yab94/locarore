<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\UseCase;

use Rore\Application\Catalog\Port\CategoryRepositoryInterface;
use Rore\Application\Catalog\Port\PackRepositoryInterface;
use Rore\Application\Catalog\Port\ProductRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Infrastructure\Persistence\MySqlPackRepository;
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
     * @return array{category: ?\Rore\Domain\Catalog\Entity\Category, products: array, packs: array, productsById: array}|null
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
