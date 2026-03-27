<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Entity\Product;
use Rore\Port\ProductRepositoryInterface;
use Rore\Port\TagRepositoryInterface;
use Rore\ValueObject\Slug;
use Rore\Service\SlugUniquenessService;
use Rore\Adapter\MySqlProductRepository;
use Rore\Adapter\MySqlTagRepository;
use RRB\Di\BindAdapter;

class CreateProductUseCase
{
    public function __construct(
        #[BindAdapter(MySqlProductRepository::class)]
        private ProductRepositoryInterface $productRepository,
        private SlugUniquenessService      $slugChecker,
        #[BindAdapter(MySqlTagRepository::class)]
        private TagRepositoryInterface $tagRepository,
    ) {}

    /**
     * @param int[] $extraCategoryIds IDs des catégories supplémentaires
     * @param string[] $tagNames Noms des tags (créés à la volée si inexistants)
     */
    public function execute(
        int     $categoryId,
        string  $name,
        ?string $description,
        int     $stock,
        float   $priceBase,
        int     $stockOnDemand       = 0,
        float   $fabricationTimeDays = 0.0,
        float   $priceExtraWeekend   = 0.0,
        float   $priceExtraWeekday   = 15.0,
        array   $extraCategoryIds    = [],
        ?string $customSlug          = null,
        array   $tagNames            = [],
        ?string $descriptionShort    = null,
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
            description:         $description,
            descriptionShort:    $descriptionShort,
            stock:               $stock,
            stockOnDemand: $stockOnDemand,
            fabricationTimeDays: max(0.0, $fabricationTimeDays),
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

        $id = $this->productRepository->save($product);

        // Synchroniser les tags (créés à la volée si nécessaire)
        $this->tagRepository->syncForProduct($id, $tagNames);

        return $id;
    }
}
