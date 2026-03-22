<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Entity\Category;
use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Domain\Catalog\ValueObject\Slug;

class CreateCategoryUseCase
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function execute(
        string  $name,
        ?string $description,
        ?int    $parentId    = null,
        ?string $customSlug  = null,
    ): void {
        $now  = new \DateTimeImmutable();
        $slug = $customSlug ? Slug::from($customSlug)->getValue()
                            : Slug::from($name)->getValue();

        $category = new Category(
            id:          null,
            parentId:    $parentId,
            name:        $name,
            slug:        $slug,
            description: $description,
            isActive:    true,
            createdAt:   $now,
            updatedAt:   $now,
        );

        $this->categoryRepository->save($category);
    }
}
