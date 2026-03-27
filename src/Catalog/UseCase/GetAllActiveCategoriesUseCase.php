<?php

declare(strict_types=1);

namespace Rore\Catalog\UseCase;

use Rore\Catalog\Port\CategoryRepositoryInterface;
use Rore\Catalog\Adapter\MySqlCategoryRepository;
use Rore\Framework\Di\BindAdapter;

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
     * @return array<int, \Rore\Catalog\Entity\Category>
     */
    public function execute(): array
    {
        return $this->categoryRepo->findRootsWithChildren();
    }
}
