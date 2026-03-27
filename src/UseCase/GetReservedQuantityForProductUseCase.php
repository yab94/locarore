<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Port\ReservationRepositoryInterface;
use Rore\Adapter\MySqlReservationRepository;
use RRB\Di\BindAdapter;

/**
 * Récupère la quantité réservée pour un produit sur une période.
 */
final class GetReservedQuantityForProductUseCase
{
    public function __construct(
        #[BindAdapter(MySqlReservationRepository::class)]
        private readonly ReservationRepositoryInterface $reservationRepo,
    ) {}

    public function execute(int $productId, string $startDate, string $endDate): int
    {
        return $this->reservationRepo->countReservedQtyForProduct($productId, $startDate, $endDate);
    }
}
