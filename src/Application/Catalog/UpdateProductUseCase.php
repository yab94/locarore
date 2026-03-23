<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Domain\Catalog\ValueObject\Slug;

class UpdateProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function execute(
        int     $id,
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
    ): void {
        $product = $this->productRepository->findById($id);
        if ($product === null) {
            throw new \RuntimeException("Produit introuvable ($id).");
        }

        $slug = $customSlug ? Slug::from($customSlug)->getValue()
                            : Slug::from($name)->getValue();

        $product->setCategoryId($categoryId);
        $product->setName($name);
        $product->setSlug($slug);
        $product->setDescription($description);
        $product->setStock($stock);
        $product->setStockOnDemand($stockOnDemand);
        $product->setPriceBase($priceBase);
        $product->setPriceExtraWeekend($priceExtraWeekend);
        $product->setPriceExtraWeekday($priceExtraWeekday);
        $product->setUpdatedAt(new \DateTimeImmutable());

        $allCats = array_unique(array_merge([$categoryId], array_map('intval', $extraCategoryIds)));
        $product->setCategoryIds($allCats);

        $this->productRepository->save($product);
    }
}
