<?php

declare(strict_types=1);

namespace Rore\Catalog\UseCase;

use Rore\Catalog\Port\ProductRepositoryInterface;
use Rore\Catalog\Port\TagRepositoryInterface;
use Rore\Catalog\ValueObject\Slug;
use Rore\Catalog\UseCase\SlugUniquenessService;
use Rore\Catalog\Adapter\MySqlProductRepository;
use Rore\Catalog\Adapter\MySqlTagRepository;
use Rore\Framework\Di\BindAdapter;

class UpdateProductUseCase
{
    public function __construct(
        #[BindAdapter(MySqlProductRepository::class)]
        private ProductRepositoryInterface $productRepository,
        private SlugUniquenessService      $slugChecker,
        #[BindAdapter(MySqlTagRepository::class)]
        private TagRepositoryInterface $tagRepository,
    ) {}

    public function execute(
        int     $id,
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
    ): void {
        $product = $this->productRepository->findById($id);
        if ($product === null) {
            throw new \RuntimeException("Produit introuvable ($id).");
        }

        $slug = $customSlug ? Slug::from($customSlug)->getValue()
                            : Slug::from($name)->getValue();

        if ($this->slugChecker->isTaken($slug, 'product', $id)) {
            throw new \DomainException("Le slug « $slug » est déjà utilisé.");
        }

        $product->setCategoryId($categoryId);
        $product->setName($name);
        $product->setSlug($slug);
        $product->setDescription($description);
        $product->setDescriptionShort($descriptionShort);
        $product->setStock($stock);
        $product->setStockOnDemand($stockOnDemand);
        $product->setFabricationTimeDays($fabricationTimeDays);
        $product->setPriceBase($priceBase);
        $product->setPriceExtraWeekend($priceExtraWeekend);
        $product->setPriceExtraWeekday($priceExtraWeekday);
        $product->setUpdatedAt(new \DateTimeImmutable());

        $allCats = array_unique(array_merge([$categoryId], array_map('intval', $extraCategoryIds)));
        $product->setCategoryIds($allCats);

        $this->productRepository->save($product);

        // Synchroniser les tags
        $this->tagRepository->syncForProduct($id, $tagNames);
    }
}
