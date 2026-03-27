<?php

declare(strict_types=1);

namespace Rore\Catalog\UseCase;

use Rore\Catalog\Port\CategoryRepositoryInterface;
use Rore\Catalog\Port\ProductRepositoryInterface;
use Rore\Catalog\Port\TagRepositoryInterface;
use Rore\Reservation\Port\ReservationRepositoryInterface;
use Rore\Catalog\Adapter\MySqlCategoryRepository;
use Rore\Catalog\Adapter\MySqlProductRepository;
use Rore\Catalog\Adapter\MySqlTagRepository;
use Rore\Reservation\Adapter\MySqlReservationRepository;
use Rore\Framework\Di\BindAdapter;

final class GetProductEditDataUseCase
{
    public function __construct(
        #[BindAdapter(MySqlProductRepository::class)]
        private readonly ProductRepositoryInterface $productRepo,
        #[BindAdapter(MySqlCategoryRepository::class)]
        private readonly CategoryRepositoryInterface $categoryRepo,
        #[BindAdapter(MySqlReservationRepository::class)]
        private readonly ReservationRepositoryInterface $reservationRepo,
        #[BindAdapter(MySqlTagRepository::class)]
        private readonly TagRepositoryInterface $tagRepo,
    ) {}

    /**
     * @return array{
     *   product: \Rore\Catalog\Entity\Product,
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
