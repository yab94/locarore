<?php

declare(strict_types=1);

namespace Rore\Catalog\UseCase;

use Rore\Catalog\Port\CategoryRepositoryInterface;
use Rore\Catalog\Adapter\MySqlCategoryRepository;
use Rore\Framework\Di\BindAdapter;

class ToggleCategoryUseCase
{
    public function __construct(
        #[BindAdapter(MySqlCategoryRepository::class)]
        private CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function execute(int $id): void
    {
        $category = $this->categoryRepository->findById($id);
        if ($category === null) {
            throw new \RuntimeException("Catégorie introuvable ($id).");
        }

        $category->toggle();
        $category->setUpdatedAt(new \DateTimeImmutable());

        $this->categoryRepository->save($category);
    }
}
