<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Entity\Category;
use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Domain\Catalog\ValueObject\Slug;
use Rore\Domain\Catalog\Service\SlugUniquenessService;

class CreateCategoryUseCase
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private SlugUniquenessService       $slugChecker,
    ) {}

    public function execute(
        string  $name,
        ?string $descriptionShort = null,
        ?string $description = null,
        ?int    $parentId    = null,
        ?string $customSlug  = null,
    ): void {
        $now  = new \DateTimeImmutable();
        $slug = $customSlug ? Slug::from($customSlug)->getValue()
                            : Slug::from($name)->getValue();

        if ($this->slugChecker->isTaken($slug, 'category')) {
            throw new \DomainException("Le slug « $slug » est déjà utilisé.");
        }

        $category = new Category(
            id:               null,
            parentId:         $parentId,
            name:             $name,
            slug:             $slug,
            descriptionShort: $descriptionShort,
            description:      $description,
            isActive:         true,
            createdAt:        $now,
            updatedAt:        $now,
        );

        $this->categoryRepository->save($category);
    }
}
