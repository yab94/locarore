<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\UseCase;

use Rore\Application\Catalog\Port\CategoryRepositoryInterface;
use Rore\Application\Catalog\Port\PackRepositoryInterface;
use Rore\Application\Catalog\Port\ProductRepositoryInterface;
use Rore\Application\Catalog\Port\TagRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlCategoryRepositoryAdapter;
use Rore\Infrastructure\Persistence\MySqlProductRepositoryAdapter;
use Rore\Infrastructure\Persistence\MySqlPackRepositoryAdapter;
use Rore\Infrastructure\Persistence\MySqlTagRepositoryAdapter;
use RRB\Di\BindAdapter;

/**
 * Récupère toutes les entités du catalogue pour le sitemap.
 */
final class GetAllCatalogItemsUseCase
{
    public function __construct(
        #[BindAdapter(MySqlCategoryRepositoryAdapter::class)]
        private readonly CategoryRepositoryInterface $categoryRepo,
        #[BindAdapter(MySqlProductRepositoryAdapter::class)]
        private readonly ProductRepositoryInterface $productRepo,
        #[BindAdapter(MySqlPackRepositoryAdapter::class)]
        private readonly PackRepositoryInterface $packRepo,
        #[BindAdapter(MySqlTagRepositoryAdapter::class)]
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
