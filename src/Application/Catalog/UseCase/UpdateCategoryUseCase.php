<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\UseCase;

use Rore\Application\Catalog\Port\CategoryRepositoryInterface;
use Rore\Domain\Catalog\ValueObject\Slug;
use Rore\Application\Catalog\Service\SlugUniquenessService;
use Rore\Application\Catalog\Port\SlugUniquenessServiceInterface;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use RRB\Di\BindAdapter;

class UpdateCategoryUseCase
{
    public function __construct(
        #[BindAdapter(MySqlCategoryRepository::class)]
        private CategoryRepositoryInterface $categoryRepository,
        #[BindAdapter(SlugUniquenessService::class)]
        private SlugUniquenessServiceInterface $slugChecker,
    ) {}

    public function execute(
        int     $id,
        string  $name,
        ?string $descriptionShort = null,
        ?string $description = null,
        ?int    $parentId   = null,
        ?string $customSlug = null,
    ): void {
        $category = $this->categoryRepository->findById($id);
        if ($category === null) {
            throw new \RuntimeException("Catégorie introuvable ($id).");
        }

        $slug = $customSlug ? Slug::from($customSlug)->getValue()
                            : Slug::from($name)->getValue();

        if ($this->slugChecker->isTaken($slug, 'category', $id)) {
            throw new \DomainException("Le slug « $slug » est déjà utilisé.");
        }

        $category->setName($name);
        $category->setSlug($slug);
        $category->setDescriptionShort($descriptionShort);
        $category->setDescription($description);
        $category->setParentId($parentId ?: null);
        $category->setUpdatedAt(new \DateTimeImmutable());

        $this->categoryRepository->save($category);
    }
}
