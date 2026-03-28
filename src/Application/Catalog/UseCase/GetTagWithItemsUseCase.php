<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\UseCase;

use Rore\Application\Catalog\Port\PackRepositoryInterface;
use Rore\Application\Catalog\Port\ProductRepositoryInterface;
use Rore\Application\Catalog\Port\TagRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlProductRepositoryAdapter;
use Rore\Infrastructure\Persistence\MySqlPackRepositoryAdapter;
use Rore\Infrastructure\Persistence\MySqlTagRepositoryAdapter;
use RRB\Di\BindAdapter;

/**
 * Récupère un tag avec ses produits et packs associés.
 */
final class GetTagWithItemsUseCase
{
    public function __construct(
        #[BindAdapter(MySqlTagRepositoryAdapter::class)]
        private readonly TagRepositoryInterface $tagRepo,
        #[BindAdapter(MySqlProductRepositoryAdapter::class)]
        private readonly ProductRepositoryInterface $productRepo,
        #[BindAdapter(MySqlPackRepositoryAdapter::class)]
        private readonly PackRepositoryInterface $packRepo,
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
