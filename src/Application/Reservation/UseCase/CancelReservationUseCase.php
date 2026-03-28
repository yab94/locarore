<?php

declare(strict_types=1);

namespace Rore\Application\Reservation\UseCase;

use Rore\Application\Reservation\Port\ReservationRepositoryInterface;
use Rore\Domain\Reservation\ValueObject\ReservationStatus;
use Rore\Infrastructure\Persistence\MySqlReservationRepositoryAdapter;
use RRB\Di\BindAdapter;

class CancelReservationUseCase
{
    public function __construct(
        #[BindAdapter(MySqlReservationRepositoryAdapter::class)]
        private ReservationRepositoryInterface $reservationRepository,
    ) {}

    public function execute(int $reservationId): void
    {
        $reservation = $this->reservationRepository->findById($reservationId);
        if ($reservation === null) {
            throw new \RuntimeException("Réservation introuvable ($reservationId).");
        }
        if ($reservation->isCancelled()) {
            throw new \RuntimeException("La réservation est déjà annulée.");
        }

        $reservation->setStatus(ReservationStatus::Cancelled);
        $reservation->setUpdatedAt(new \DateTimeImmutable());

        $this->reservationRepository->update($reservation);
    }
}
