<?php

declare(strict_types=1);

namespace Rore\Application\Reservation;

use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Domain\Reservation\Repository\ReservationRepositoryInterface;
use Rore\Domain\Reservation\Service\AvailabilityService;

class ConfirmReservationUseCase
{
    public function __construct(
        private ReservationRepositoryInterface $reservationRepository,
        private ProductRepositoryInterface     $productRepository,
        private AvailabilityService            $availabilityService,
    ) {}

    public function execute(int $reservationId): void
    {
        $reservation = $this->reservationRepository->findById($reservationId);
        if ($reservation === null) {
            throw new \RuntimeException("Réservation introuvable ($reservationId).");
        }
        if (!$reservation->isPending()) {
            throw new \RuntimeException("La réservation n'est pas en attente.");
        }

        // Vérification de disponibilité pour chaque ligne
        foreach ($reservation->getItems() as $item) {
            $product = $this->productRepository->findById($item->getProductId());
            if ($product === null) {
                throw new \RuntimeException("Produit introuvable ({$item->getProductId()}).");
            }

            if (!$this->availabilityService->isAvailable(
                $product,
                $item->getQuantity(),
                $reservation->getStartDate(),
                $reservation->getEndDate(),
            )) {
                throw new \RuntimeException(
                    "Stock insuffisant pour le produit « {$product->getName()} »."
                );
            }
        }

        $reservation->setStatus('confirmed');
        $reservation->setUpdatedAt(new \DateTimeImmutable());

        $this->reservationRepository->update($reservation);
    }
}
