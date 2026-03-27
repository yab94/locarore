<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
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
