<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\UseCase;

use Rore\Application\Catalog\Port\CategoryRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlCategoryRepositoryAdapter;
use RRB\Di\BindAdapter;

/**
 * Récupère toutes les catégories actives.
 */
final class GetAllActiveCategoriesUseCase
{
    public function __construct(
        #[BindAdapter(MySqlCategoryRepositoryAdapter::class)]
        private readonly CategoryRepositoryInterface $categoryRepo,
    ) {}

    /**
     * @return array<int, \Rore\Domain\Catalog\Entity\Category>
     */
    public function execute(): array
    {
        return $this->categoryRepo->findRootsWithChildren();
    }
}
