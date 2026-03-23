<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Domain\Catalog\ValueObject\Slug;
use Rore\Domain\Catalog\Service\SlugUniquenessChecker;

class UpdateProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private SlugUniquenessChecker      $slugChecker,
    ) {}

    public function execute(
        int     $id,
        int     $categoryId,
        string  $name,
        ?string $description,
        int     $stock,
        float   $priceBase,
        int     $stockOnDemand    = 0,
        float   $fabricationTimeDays = 0.0,
        float   $priceExtraWeekend = 0.0,
        float   $priceExtraWeekday = 15.0,
        array   $extraCategoryIds = [],
        ?string $customSlug       = null,
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
    }
}
