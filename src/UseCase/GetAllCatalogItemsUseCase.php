<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Port\CategoryRepositoryInterface;
use Rore\Port\PackRepositoryInterface;
use Rore\Port\ProductRepositoryInterface;
use Rore\Port\TagRepositoryInterface;
use Rore\Adapter\MySqlCategoryRepository;
use Rore\Adapter\MySqlProductRepository;
use Rore\Adapter\MySqlPackRepository;
use Rore\Adapter\MySqlTagRepository;
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
