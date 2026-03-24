<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Domain\Catalog\Repository\PackRepositoryInterface;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;

/**
 * Récupère une catégorie avec ses produits et packs.
 */
final class GetCategoryWithItemsUseCase
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepo,
        private readonly ProductRepositoryInterface  $productRepo,
        private readonly PackRepositoryInterface     $packRepo,
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
