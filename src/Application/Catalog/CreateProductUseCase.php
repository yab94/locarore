<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Entity\Product;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Domain\Catalog\ValueObject\Slug;
use Rore\Domain\Catalog\Service\SlugUniquenessChecker;

class CreateProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private SlugUniquenessChecker      $slugChecker,
    ) {}

    /**
     * @param int[] $extraCategoryIds IDs des catégories supplémentaires
     */
    public function execute(
        int     $categoryId,
        string  $name,
        ?string $description,
        int     $stock,
        float   $priceBase,
        int     $stockOnDemand    = 0,
        float   $priceExtraWeekend = 0.0,
        float   $priceExtraWeekday = 15.0,
        array   $extraCategoryIds = [],
        ?string $customSlug       = null,
    ): int {
        $now  = new \DateTimeImmutable();
        $slug = $customSlug ? Slug::from($customSlug)->getValue()
                            : Slug::from($name)->getValue();
        
        if ($this->slugChecker->isTaken($slug, 'product')) {
            throw new \DomainException("Le slug « $slug » est déjà utilisé.");
        }
        
        $product = new Product(
            id:            null,
            categoryId:    $categoryId,
            name:          $name,
            slug:          $slug,
            description:   $description,
            stock:         $stock,
            stockOnDemand: $stockOnDemand,
            priceBase:     $priceBase,
            priceExtraWeekend:  $priceExtraWeekend,
            priceExtraWeekday: $priceExtraWeekday,
            isActive:      true,
            createdAt:     $now,
            updatedAt:     $now,
        );

        // Toutes les catégories (principale + extra)
        $allCats = array_unique(array_merge([$categoryId], array_map('intval', $extraCategoryIds)));
        $product->setCategoryIds($allCats);

        return $this->productRepository->save($product);
    }
}
