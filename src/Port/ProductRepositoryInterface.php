<?php

declare(strict_types=1);

namespace Rore\Port;

use Rore\Entity\Product;
use Rore\Entity\ProductPhoto;
use Rore\Adapter\MySqlProductRepository;

interface ProductRepositoryInterface
{
    /** @return Product[] */
    public function findAll(): array;

    /** @return Product[] */
    public function findAllActive(): array;

    /** @return Product[] */
    public function findActiveByCategorySlug(string $slug): array;

    /** @return Product[] */
    public function findActiveByTagSlug(string $slug): array;

    public function findById(int $id): ?Product;

    public function findBySlug(string $slug): ?Product;

    public function save(Product $product): int;

    public function delete(int $id): void;

    public function savePhoto(ProductPhoto $photo): void;

    public function deletePhoto(int $photoId): void;

    public function updatePhotoDescription(int $photoId, string $description): void;

    public function findPhotoById(int $photoId): ?ProductPhoto;

    /** @return ProductPhoto[] */
    public function findPhotosByProductId(int $productId): array;

    /** @param array<int,int> $photoIdToOrder  photoId => new sort_order */
    public function updatePhotoSortOrders(array $photoIdToOrder): void;
}
