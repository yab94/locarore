<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Port\CategoryRepositoryInterface;
use Rore\Adapter\MySqlCategoryRepository;
use RRB\Di\BindAdapter;

/**
 * Récupère toutes les catégories du catalogue.
 */
final class GetAllCategoriesUseCase
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
        return $this->categoryRepo->findAll();
    }
}
