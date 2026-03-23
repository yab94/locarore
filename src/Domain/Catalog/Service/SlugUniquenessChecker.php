<?php

declare(strict_types=1);

namespace Rore\Domain\Catalog\Service;

use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Domain\Catalog\Repository\PackRepositoryInterface;

/**
 * Vérifie qu'un slug est unique sur l'ensemble du catalogue
 * (catégories, produits ET packs partagent le même espace d'URL).
 */
class SlugUniquenessChecker
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepo,
        private ProductRepositoryInterface  $productRepo,
        private PackRepositoryInterface     $packRepo,
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
