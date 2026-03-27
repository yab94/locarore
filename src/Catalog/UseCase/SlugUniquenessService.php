<?php

declare(strict_types=1);

namespace Rore\Catalog\UseCase;

use Rore\Catalog\Port\CategoryRepositoryInterface;
use Rore\Catalog\Port\ProductRepositoryInterface;
use Rore\Catalog\Port\PackRepositoryInterface;
use Rore\Catalog\Adapter\MySqlCategoryRepository;
use Rore\Catalog\Adapter\MySqlProductRepository;
use Rore\Catalog\Adapter\MySqlPackRepository;
use Rore\Framework\Di\BindAdapter;

/**
 * Vérifie qu'un slug est unique sur l'ensemble du catalogue
 * (catégories, produits ET packs partagent le même espace d'URL).
 */
final class SlugUniquenessService
{
    public function __construct(
        #[BindAdapter(MySqlCategoryRepository::class)]
        private CategoryRepositoryInterface $categoryRepo,
        #[BindAdapter(MySqlProductRepository::class)]
        private ProductRepositoryInterface $productRepo,
        #[BindAdapter(MySqlPackRepository::class)]
        private PackRepositoryInterface $packRepo,
    ) {}

    /**
     * Retourne true si le slug est déjà utilisé par une autre entité.
     *
     * @param string      $slug       Le slug à tester
     * @param string      $type       'category' | 'product' | 'pack'
     * @param int|null    $excludeId  ID de l'entité en cours de modification
     *                                (null = création)
     */
    public function isTaken(string $slug, string $type, ?int $excludeId = null): bool
    {
        // Catégorie
        $cat = $this->categoryRepo->findBySlug($slug);
        if ($cat !== null) {
            if ($type !== 'category' || $cat->getId() !== $excludeId) {
                return true;
            }
        }

        // Produit
        $product = $this->productRepo->findBySlug($slug);
        if ($product !== null) {
            if ($type !== 'product' || $product->getId() !== $excludeId) {
                return true;
            }
        }

        // Pack
        $pack = $this->packRepo->findBySlug($slug);
        if ($pack !== null) {
            if ($type !== 'pack' || $pack->getId() !== $excludeId) {
                return true;
            }
        }

        return false;
    }
}
