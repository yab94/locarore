<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Entity\Pack;
use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Domain\Catalog\Repository\PackRepositoryInterface;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;

/**
 * Récupère un pack avec toutes ses données liées.
 */
final class GetPackWithDetailsUseCase
{
    public function __construct(
        private readonly PackRepositoryInterface     $packRepo,
        private readonly ProductRepositoryInterface  $productRepo,
        private readonly CategoryRepositoryInterface $categoryRepo,
    ) {}

    /**
     * @return array{pack: Pack, productsById: array, allCategories: array}|null
     */
    public function execute(string $slug): ?array
    {
        $pack = $this->packRepo->findBySlug($slug);
        
        if ($pack === null) {
            return null;
        }

        // Produits indexés par id pour afficher les détails du pack
        $allProducts  = $this->productRepo->findAll();
        $productsById = [];
        foreach ($allProducts as $p) {
            $productsById[$p->getId()] = $p;
        }

        return [
            'pack'          => $pack,
            'productsById'  => $productsById,
            'allCategories' => $this->categoryRepo->findAllActive(),
        ];
    }
}
