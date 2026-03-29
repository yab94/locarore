<?php

declare(strict_types=1);

namespace Rore\Application\Reservation\UseCase;

use Rore\Application\Reservation\Port\ReservationRepositoryInterface;

/**
 * Récupère la quantité réservée pour un produit sur une période.
 */
final class GetReservedQuantityForProductUseCase
{
    public function __construct(
        private readonly ReservationRepositoryInterface $reservationRepo,
    ) {}

    public function execute(int $productId, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): int
    {
        return $this->reservationRepo->countReservedQtyForProduct(
            $productId,
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
        );
    }
}
