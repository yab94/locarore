<?php

declare(strict_types=1);

namespace Rore\Application\Reservation\UseCase;

use Rore\Application\Catalog\Port\ProductRepositoryInterface;
use Rore\Application\Reservation\Port\ReservationRepositoryInterface;
use Rore\Domain\Reservation\ValueObject\ReservationStatus;
use Rore\Application\Reservation\Port\AvailabilityServiceInterface;

class ConfirmReservationUseCase
{
    public function __construct(
        private ReservationRepositoryInterface $reservationRepository,
        private ProductRepositoryInterface $productRepository,
        private AvailabilityServiceInterface   $availabilityService,
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

        $reservation->setStatus(ReservationStatus::Confirmed);
        $reservation->setUpdatedAt(new \DateTimeImmutable());

        $this->reservationRepository->update($reservation);
    }
}
