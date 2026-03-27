<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Port\CategoryRepositoryInterface;
use Rore\Port\ProductRepositoryInterface;
use Rore\Port\TagRepositoryInterface;
use Rore\Port\ReservationRepositoryInterface;
use Rore\Adapter\MySqlCategoryRepository;
use Rore\Adapter\MySqlProductRepository;
use Rore\Adapter\MySqlTagRepository;
use Rore\Adapter\MySqlReservationRepository;
use RRB\Di\BindAdapter;

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
     *   product: \Rore\Entity\Product,
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
