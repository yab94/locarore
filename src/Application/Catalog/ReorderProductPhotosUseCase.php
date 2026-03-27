<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use RRB\Di\BindAdapter;

class ReorderProductPhotosUseCase
{
    public function __construct(
        #[BindAdapter(MySqlProductRepository::class)]
        private ProductRepositoryInterface $productRepository,
    ) {}

    /**
     * @param int   $productId
     * @param int[] $orderedPhotoIds  IDs dans le nouvel ordre (index 0 = prioritaire)
     */
    public function execute(int $productId, array $orderedPhotoIds): void
    {
        $existing = $this->productRepository->findPhotosByProductId($productId);
        $existingIds = array_map(fn($p) => $p->getId(), $existing);

        $photoIdToOrder = [];
        foreach ($orderedPhotoIds as $position => $photoId) {
            $photoId = (int) $photoId;
            if (!in_array($photoId, $existingIds, true)) {
                throw new \RuntimeException("Photo $photoId n'appartient pas au produit $productId.");
            }
            $photoIdToOrder[$photoId] = $position;
        }

        $this->productRepository->updatePhotoSortOrders($photoIdToOrder);
    }
}
