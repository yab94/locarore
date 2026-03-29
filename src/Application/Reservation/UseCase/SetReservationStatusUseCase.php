<?php

declare(strict_types=1);

namespace Rore\Application\Reservation\UseCase;

use Rore\Application\Reservation\Port\ReservationRepositoryInterface;
use Rore\Domain\Reservation\ValueObject\ReservationStatus;

/**
 * Transitions manuelles de statut (retours arrière, corrections admin).
 * Statuts autorisés : pending, quoted, confirmed, cancelled
 */
class SetReservationStatusUseCase
{
    public function __construct(
        private ReservationRepositoryInterface $repo,
    ) {}

    public function execute(int $id, string $newStatus): void
    {
        try {
            $status = ReservationStatus::from($newStatus);
        } catch (\ValueError) {
            throw new \InvalidArgumentException("Statut invalide : $newStatus");
        }

        $reservation = $this->repo->findById($id);
        if (!$reservation) {
            throw new \RuntimeException('Réservation introuvable.');
        }

        $reservation->setStatus($status);
        $reservation->setUpdatedAt(new \DateTimeImmutable());
        $this->repo->update($reservation);
    }
}
