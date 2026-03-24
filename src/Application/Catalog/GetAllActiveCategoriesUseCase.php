<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;

/**
 * Récupère toutes les catégories actives.
 */
final class GetAllActiveCategoriesUseCase
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepo,
    ) {}

    /**
     * @return array<int, \Rore\Domain\Catalog\Entity\Category>
     */
    public function execute(): array
    {
        return $this->categoryRepo->findAllActive();
    }
}
