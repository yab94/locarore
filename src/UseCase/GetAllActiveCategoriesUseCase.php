<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Port\CategoryRepositoryInterface;
use Rore\Adapter\MySqlCategoryRepository;
use RRB\Di\BindAdapter;

/**
 * Récupère toutes les catégories actives.
 */
final class GetAllActiveCategoriesUseCase
{
    public function __construct(
        #[BindAdapter(MySqlCategoryRepository::class)]
        private readonly CategoryRepositoryInterface $categoryRepo,
    ) {}

    /**
     * @return array<int, \Rore\Entity\Category>
     */
    public function execute(): array
    {
        return $this->categoryRepo->findRootsWithChildren();
    }
}
