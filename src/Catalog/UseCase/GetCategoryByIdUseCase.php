<?php

declare(strict_types=1);

namespace Rore\Catalog\UseCase;

use Rore\Catalog\Entity\Category;
use Rore\Catalog\Port\CategoryRepositoryInterface;
use Rore\Catalog\Adapter\MySqlCategoryRepository;
use Rore\Framework\Di\BindAdapter;

/**
 * Récupère une catégorie par son ID.
 */
final class GetCategoryByIdUseCase
{
    public function __construct(
        #[BindAdapter(MySqlCategoryRepository::class)]
        private readonly CategoryRepositoryInterface $categoryRepo,
    ) {}

    public function execute(int $id): ?Category
    {
        return $this->categoryRepo->findById($id);
    }
}
