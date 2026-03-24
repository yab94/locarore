<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Entity\Category;
use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;

/**
 * Récupère une catégorie par son chemin hiérarchique.
 * Extrait le slug du dernier segment du path.
 */
final class GetCategoryByPathUseCase
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepo,
    ) {}

    public function execute(string $path): ?Category
    {
        // Le dernier segment est le slug de la catégorie
        $segments = explode('/', trim($path, '/'));
        $slug     = end($segments);

        return $this->categoryRepo->findBySlug($slug);
    }
}
