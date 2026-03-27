<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Port\CategoryRepositoryInterface;
use Rore\Port\ProductRepositoryInterface;
use Rore\Port\TagRepositoryInterface;
use Rore\Adapter\MySqlCategoryRepository;
use Rore\Adapter\MySqlProductRepository;
use Rore\Adapter\MySqlTagRepository;
use RRB\Di\BindAdapter;

/**
 * Récupère les données pour la page d'accueil.
 */
final class GetHomePageDataUseCase
{
    public function __construct(
        #[BindAdapter(MySqlCategoryRepository::class)]
        private readonly CategoryRepositoryInterface $categoryRepo,
        #[BindAdapter(MySqlProductRepository::class)]
        private readonly ProductRepositoryInterface $productRepo,
        #[BindAdapter(MySqlTagRepository::class)]
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
