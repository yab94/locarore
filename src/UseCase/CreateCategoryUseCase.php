<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Entity\Category;
use Rore\Port\CategoryRepositoryInterface;
use Rore\ValueObject\Slug;
use Rore\Service\SlugUniquenessService;
use Rore\Adapter\MySqlCategoryRepository;
use RRB\Di\BindAdapter;

class CreateCategoryUseCase
{
    public function __construct(
        #[BindAdapter(MySqlCategoryRepository::class)]
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
