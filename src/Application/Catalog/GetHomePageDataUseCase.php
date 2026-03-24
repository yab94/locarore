<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Domain\Catalog\Repository\TagRepositoryInterface;

/**
 * Récupère les données pour la page d'accueil.
 */
final class GetHomePageDataUseCase
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepo,
        private readonly ProductRepositoryInterface  $productRepo,
        private readonly TagRepositoryInterface      $tagRepo,
    ) {}

    /**
     * @return array{categories: array, products: array, tags: array}
     */
    public function execute(): array
    {
        return [
            'categories' => $this->categoryRepo->findRootsWithChildren(),
            'products'   => $this->productRepo->findAllActive(),
            'tags'       => $this->tagRepo->findAll(),
        ];
    }
}
