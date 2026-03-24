<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Domain\Catalog\Repository\PackRepositoryInterface;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Domain\Catalog\Repository\TagRepositoryInterface;

/**
 * Récupère toutes les entités du catalogue pour le sitemap.
 */
final class GetAllCatalogItemsUseCase
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepo,
        private readonly ProductRepositoryInterface  $productRepo,
        private readonly PackRepositoryInterface     $packRepo,
        private readonly TagRepositoryInterface      $tagRepo,
    ) {}

    /**
     * @return array{categories: array, products: array, packs: array, tags: array}
     */
    public function execute(): array
    {
        return [
            'categories' => $this->categoryRepo->findAllActive(),
            'products'   => $this->productRepo->findAllActive(),
            'packs'      => $this->packRepo->findAllActive(),
            'tags'       => $this->tagRepo->findAll(),
        ];
    }
}
