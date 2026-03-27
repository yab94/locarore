<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Domain\Catalog\ValueObject\Slug;
use Rore\Domain\Catalog\Service\SlugUniquenessService;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use RRB\Di\BindAdapter;

class UpdateCategoryUseCase
{
    public function __construct(
        #[BindAdapter(MySqlCategoryRepository::class)]
        private CategoryRepositoryInterface $categoryRepository,
        private SlugUniquenessService       $slugChecker,
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
