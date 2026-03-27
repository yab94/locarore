<?php

declare(strict_types=1);

namespace Rore\Application\Reservation;

use Rore\Domain\Reservation\Repository\ReservationRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlReservationRepository;
use Rore\Framework\Di\BindAdapter;

class CancelReservationUseCase
{
    public function __construct(
        #[BindAdapter(MySqlReservationRepository::class)]
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

        $reservation->setStatus('cancelled');
        $reservation->setUpdatedAt(new \DateTimeImmutable());

        $this->reservationRepository->update($reservation);
    }
}
