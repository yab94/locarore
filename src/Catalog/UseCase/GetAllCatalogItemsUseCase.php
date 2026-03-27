<?php

declare(strict_types=1);

namespace Rore\Catalog\UseCase;

use Rore\Catalog\Port\CategoryRepositoryInterface;
use Rore\Catalog\Port\PackRepositoryInterface;
use Rore\Catalog\Port\ProductRepositoryInterface;
use Rore\Catalog\Port\TagRepositoryInterface;
use Rore\Catalog\Adapter\MySqlCategoryRepository;
use Rore\Catalog\Adapter\MySqlProductRepository;
use Rore\Catalog\Adapter\MySqlPackRepository;
use Rore\Catalog\Adapter\MySqlTagRepository;
use Rore\Framework\Di\BindAdapter;

/**
 * Récupère toutes les entités du catalogue pour le sitemap.
 */
final class GetAllCatalogItemsUseCase
{
    public function __construct(
        #[BindAdapter(MySqlCategoryRepository::class)]
        private readonly CategoryRepositoryInterface $categoryRepo,
        #[BindAdapter(MySqlProductRepository::class)]
        private readonly ProductRepositoryInterface $productRepo,
        #[BindAdapter(MySqlPackRepository::class)]
        private readonly PackRepositoryInterface $packRepo,
        #[BindAdapter(MySqlTagRepository::class)]
        private readonly TagRepositoryInterface $tagRepo,
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
