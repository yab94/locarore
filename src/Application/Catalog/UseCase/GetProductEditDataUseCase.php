<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\UseCase;

use Rore\Application\Catalog\Port\CategoryRepositoryInterface;
use Rore\Application\Catalog\Port\ProductRepositoryInterface;
use Rore\Application\Catalog\Port\TagRepositoryInterface;
use Rore\Application\Reservation\Port\ReservationRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlCategoryRepositoryAdapter;
use Rore\Infrastructure\Persistence\MySqlProductRepositoryAdapter;
use Rore\Infrastructure\Persistence\MySqlTagRepositoryAdapter;
use Rore\Infrastructure\Persistence\MySqlReservationRepositoryAdapter;
use RRB\Di\BindAdapter;

final class GetProductEditDataUseCase
{
    public function __construct(
        #[BindAdapter(MySqlProductRepositoryAdapter::class)]
        private readonly ProductRepositoryInterface $productRepo,
        #[BindAdapter(MySqlCategoryRepositoryAdapter::class)]
        private readonly CategoryRepositoryInterface $categoryRepo,
        #[BindAdapter(MySqlReservationRepositoryAdapter::class)]
        private readonly ReservationRepositoryInterface $reservationRepo,
        #[BindAdapter(MySqlTagRepositoryAdapter::class)]
        private readonly TagRepositoryInterface $tagRepo,
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
