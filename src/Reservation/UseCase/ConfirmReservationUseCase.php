<?php

declare(strict_types=1);

namespace Rore\Reservation\UseCase;

use Rore\Catalog\Port\ProductRepositoryInterface;
use Rore\Reservation\Port\ReservationRepositoryInterface;
use Rore\Reservation\UseCase\AvailabilityService;
use Rore\Catalog\Adapter\MySqlProductRepository;
use Rore\Reservation\Adapter\MySqlReservationRepository;
use Rore\Framework\Di\BindAdapter;

class ConfirmReservationUseCase
{
    public function __construct(
        #[BindAdapter(MySqlReservationRepository::class)]
        private ReservationRepositoryInterface $reservationRepository,
        #[BindAdapter(MySqlProductRepository::class)]
        private ProductRepositoryInterface $productRepository,
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
