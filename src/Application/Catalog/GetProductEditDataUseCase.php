<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Domain\Catalog\Repository\TagRepositoryInterface;
use Rore\Domain\Reservation\Repository\ReservationRepositoryInterface;

final class GetProductEditDataUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface     $productRepo,
        private readonly CategoryRepositoryInterface    $categoryRepo,
        private readonly ReservationRepositoryInterface $reservationRepo,
        private readonly TagRepositoryInterface         $tagRepo,
    ) {}

    /**
     * @return array{
     *   product: \Rore\Domain\Catalog\Entity\Product,
     *   categories: array,
     *   calendarEvents: array,
     *   productTags: array,
     * }
     */
    public function execute(int $productId): array
    {
        $product = $this->productRepo->findById($productId);
        if ($product === null) {
            throw new \RuntimeException("Produit introuvable ($productId).");
        }

        return [
            'product'        => $product,
            'categories'     => $this->categoryRepo->findAll(),
            'calendarEvents' => $this->reservationRepo->getReservedPeriodsByProduct($productId),
            'productTags'    => $this->tagRepo->findByProductId($productId),
        ];
    }
}
