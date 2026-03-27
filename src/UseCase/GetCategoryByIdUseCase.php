<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Entity\Category;
use Rore\Port\CategoryRepositoryInterface;
use Rore\Adapter\MySqlCategoryRepository;
use RRB\Di\BindAdapter;

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
