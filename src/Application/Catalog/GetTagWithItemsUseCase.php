<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Repository\PackRepositoryInterface;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Domain\Catalog\Repository\TagRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Infrastructure\Persistence\MySqlPackRepository;
use Rore\Infrastructure\Persistence\MySqlTagRepository;
use RRB\Di\BindAdapter;

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
