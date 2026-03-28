<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\UseCase;

use Rore\Domain\Catalog\Entity\Category;
use Rore\Application\Catalog\Port\CategoryRepositoryInterface;
use Rore\Domain\Catalog\ValueObject\Slug;
use Rore\Application\Catalog\Service\SlugUniquenessService;
use Rore\Application\Catalog\Port\SlugUniquenessServiceInterface;
use Rore\Infrastructure\Persistence\MySqlCategoryRepositoryAdapter;
use RRB\Di\BindAdapter;

class CreateCategoryUseCase
{
    public function __construct(
        #[BindAdapter(MySqlCategoryRepositoryAdapter::class)]
        private CategoryRepositoryInterface $categoryRepository,
        #[BindAdapter(SlugUniquenessService::class)]
        private SlugUniquenessServiceInterface $slugChecker,
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
