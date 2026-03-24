<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Repository\PackRepositoryInterface;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Domain\Catalog\Repository\TagRepositoryInterface;

/**
 * Récupère un tag avec ses produits et packs associés.
 */
final class GetTagWithItemsUseCase
{
    public function __construct(
        private readonly TagRepositoryInterface     $tagRepo,
        private readonly ProductRepositoryInterface $productRepo,
        private readonly PackRepositoryInterface    $packRepo,
    ) {}

    /**
     * @return array{tag: ?\Rore\Domain\Catalog\Entity\Tag, products: array, packs: array, productsById: array}|null
     */
    public function execute(string $slug): ?array
    {
        $tag = $this->tagRepo->findBySlug($slug);
        
        if ($tag === null) {
            return null;
        }

        $products = $this->productRepo->findActiveByTagSlug($slug);
        $packs    = $this->packRepo->findActiveByTagSlug($slug);

        // Produits indexés par id pour les pack-cards
        $allProducts  = $this->productRepo->findAll();
        $productsById = [];
        foreach ($allProducts as $p) {
            $productsById[$p->getId()] = $p;
        }

        return [
            'tag'          => $tag,
            'products'     => $products,
            'packs'        => $packs,
            'productsById' => $productsById,
        ];
    }
}
