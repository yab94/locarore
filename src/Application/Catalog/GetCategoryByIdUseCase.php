<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Entity\Category;
use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
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
