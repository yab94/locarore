<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;

/**
 * Récupère toutes les catégories du catalogue.
 */
final class GetAllCategoriesUseCase
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepo,
    ) {}

    /**
     * @return array<int, \Rore\Domain\Catalog\Entity\Category>
     */
    public function execute(): array
    {
        return $this->categoryRepo->findAll();
    }
}
