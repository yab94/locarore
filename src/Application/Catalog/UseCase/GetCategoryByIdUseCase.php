<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\UseCase;

use Rore\Domain\Catalog\Entity\Category;
use Rore\Application\Catalog\Port\CategoryRepositoryInterface;

/**
 * Récupère une catégorie par son ID.
 */
final class GetCategoryByIdUseCase
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepo,
    ) {}

    public function execute(int $id): ?Category
    {
        return $this->categoryRepo->findById($id);
    }
}
