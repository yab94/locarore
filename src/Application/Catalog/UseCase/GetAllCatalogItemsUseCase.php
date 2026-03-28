<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\UseCase;

use Rore\Application\Catalog\Port\CategoryRepositoryInterface;
use Rore\Application\Catalog\Port\PackRepositoryInterface;
use Rore\Application\Catalog\Port\ProductRepositoryInterface;
use Rore\Application\Catalog\Port\TagRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Infrastructure\Persistence\MySqlPackRepository;
use Rore\Infrastructure\Persistence\MySqlTagRepository;
use RRB\Di\BindAdapter;

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
