<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\UseCase;

use Rore\Application\Catalog\Port\CategoryRepositoryInterface;
use Rore\Application\Catalog\Port\ProductRepositoryInterface;
use Rore\Application\Catalog\Port\TagRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlCategoryRepositoryAdapter;
use Rore\Infrastructure\Persistence\MySqlProductRepositoryAdapter;
use Rore\Infrastructure\Persistence\MySqlTagRepositoryAdapter;
use RRB\Di\BindAdapter;

/**
 * Récupère les données pour la page d'accueil.
 */
final class GetHomePageDataUseCase
{
    public function __construct(
        #[BindAdapter(MySqlCategoryRepositoryAdapter::class)]
        private readonly CategoryRepositoryInterface $categoryRepo,
        #[BindAdapter(MySqlProductRepositoryAdapter::class)]
        private readonly ProductRepositoryInterface $productRepo,
        #[BindAdapter(MySqlTagRepositoryAdapter::class)]
        private readonly TagRepositoryInterface $tagRepo,
    ) {}

    /**
     * @return array{categories: array, products: array, tags: array}
     */
    public function execute(): array
    {
        return [
            'categories' => $this->categoryRepo->findRootsWithChildren(),
            'products'   => $this->productRepo->findAllActive(),
            'tags'       => $this->tagRepo->findAll(),
        ];
    }
}
