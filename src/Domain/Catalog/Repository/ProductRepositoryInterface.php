<?php

declare(strict_types=1);

namespace Rore\Domain\Catalog\Repository;

use Rore\Domain\Catalog\Entity\Product;
use Rore\Domain\Catalog\Entity\ProductPhoto;

interface ProductRepositoryInterface
{
    /** @return Product[] */
    public function findAll(): array;

    /** @return Product[] */
    public function findAllActive(): array;

    /** @return Product[] */
    public function findActiveByCategorySlug(string $slug): array;

    public function findById(int $id): ?Product;

    public function findBySlug(string $slug): ?Product;

    public function save(Product $product): int;

    public function delete(int $id): void;

    public function savePhoto(ProductPhoto $photo): void;

    public function deletePhoto(int $photoId): void;

    public function findPhotoById(int $photoId): ?ProductPhoto;

    /** @return ProductPhoto[] */
    public function findPhotosByProductId(int $productId): array;
}
