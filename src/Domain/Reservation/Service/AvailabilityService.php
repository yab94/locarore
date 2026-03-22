<?php

declare(strict_types=1);

namespace Rore\Domain\Reservation\Service;

use Rore\Domain\Reservation\Repository\ReservationRepositoryInterface;

/**
 * Calcule la disponibilité d'un produit sur une plage de dates.
 * Seules les réservations au statut "confirmed" consomment du stock.
 */
class AvailabilityService
{
    public function __construct(
        private ReservationRepositoryInterface $reservationRepository,
    ) {}

    public function getAvailableQuantity(
        int                $productId,
        int                $totalStock,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end
    ): int {
        $reservations = $this->reservationRepository->findConfirmedOverlapping($start, $end);

        $consumed = 0;
        foreach ($reservations as $reservation) {
            foreach ($reservation->getItems() as $item) {
                if ($item->getProductId() === $productId) {
                    $consumed += $item->getQuantity();
                }
            }
        }

        return max(0, $totalStock - $consumed);
    }

    public function isAvailable(
        int                $productId,
        int                $totalStock,
        int                $requestedQty,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end
    ): bool {
        return $this->getAvailableQuantity($productId, $totalStock, $start, $end) >= $requestedQty;
    }
}
