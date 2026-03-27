<?php

declare(strict_types=1);

namespace Rore\Catalog\UseCase;

use Rore\Catalog\Port\PackRepositoryInterface;
use Rore\Catalog\Port\ProductRepositoryInterface;
use Rore\Catalog\Port\TagRepositoryInterface;
use Rore\Catalog\Adapter\MySqlProductRepository;
use Rore\Catalog\Adapter\MySqlPackRepository;
use Rore\Catalog\Adapter\MySqlTagRepository;
use Rore\Framework\Di\BindAdapter;

/**
 * Récupère un tag avec ses produits et packs associés.
 */
final class GetTagWithItemsUseCase
{
    public function __construct(
        #[BindAdapter(MySqlTagRepository::class)]
        private readonly TagRepositoryInterface $tagRepo,
        #[BindAdapter(MySqlProductRepository::class)]
        private readonly ProductRepositoryInterface $productRepo,
        #[BindAdapter(MySqlPackRepository::class)]
        private readonly PackRepositoryInterface $packRepo,
    ) {}

    /**
     * @return array{tag: ?\Rore\Catalog\Entity\Tag, products: array, packs: array, productsById: array}|null
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
