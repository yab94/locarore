<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\UseCase;

use Rore\Domain\Catalog\Entity\Pack;
use Rore\Application\Catalog\Port\CategoryRepositoryInterface;
use Rore\Application\Catalog\Port\PackRepositoryInterface;
use Rore\Application\Catalog\Port\ProductRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlCategoryRepositoryAdapter;
use Rore\Infrastructure\Persistence\MySqlProductRepositoryAdapter;
use Rore\Infrastructure\Persistence\MySqlPackRepositoryAdapter;
use RRB\Di\BindAdapter;

/**
 * Récupère un pack avec toutes ses données liées.
 */
final class GetPackWithDetailsUseCase
{
    public function __construct(
        #[BindAdapter(MySqlPackRepositoryAdapter::class)]
        private readonly PackRepositoryInterface $packRepo,
        #[BindAdapter(MySqlProductRepositoryAdapter::class)]
        private readonly ProductRepositoryInterface $productRepo,
        #[BindAdapter(MySqlCategoryRepositoryAdapter::class)]
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

        $allCategories = $this->categoryRepo->findAllActive();

        // Pour chaque slot catégorie, charger les produits disponibles de cette catégorie
        $slotsWithProducts = [];
        foreach ($pack->getItems() as $item) {
            if (!$item->isSlot()) continue;
            $category = null;
            foreach ($allCategories as $cat) {
                if ($cat->getId() === $item->getCategoryId()) {
                    $category = $cat;
                    break;
                }
            }
            // Filtrer les produits actifs appartenant à cette catégorie (catégorie principale)
            $categoryProducts = array_values(array_filter(
                $allProducts,
                fn($p) => $p->isActive() && $p->getCategoryId() === $item->getCategoryId()
            ));
            $slotsWithProducts[$item->getId()] = [
                'slot'     => $item,
                'category' => $category,
                'products' => $categoryProducts,
            ];
        }

        return [
            'pack'              => $pack,
            'productsById'      => $productsById,
            'allCategories'     => $allCategories,
            'slotsWithProducts' => $slotsWithProducts,
        ];
    }
}
