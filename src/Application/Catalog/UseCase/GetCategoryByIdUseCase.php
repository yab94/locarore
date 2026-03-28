<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\UseCase;

use Rore\Domain\Catalog\Entity\Category;
use Rore\Application\Catalog\Port\CategoryRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlCategoryRepositoryAdapter;
use RRB\Di\BindAdapter;

/**
 * Récupère une catégorie par son ID.
 */
final class GetCategoryByIdUseCase
{
    public function __construct(
        #[BindAdapter(MySqlCategoryRepositoryAdapter::class)]
        private readonly CategoryRepositoryInterface $categoryRepo,
    ) {}

    public function execute(int $id): ?Category
    {
        return $this->categoryRepo->findById($id);
    }
}
