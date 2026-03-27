<?php

declare(strict_types=1);

namespace Rore\Application\Reservation\UseCase;

use Rore\Application\Reservation\Port\ReservationRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlReservationRepository;
use Rore\Framework\Di\BindAdapter;

/**
 * Transitions manuelles de statut (retours arrière, corrections admin).
 * Statuts autorisés : pending, quoted, confirmed, cancelled
 */
class SetReservationStatusUseCase
{
    private const ALLOWED = ['pending', 'quoted', 'confirmed', 'cancelled'];

    public function __construct(
        #[BindAdapter(MySqlReservationRepository::class)]
        private ReservationRepositoryInterface $repo,
    ) {}

    public function execute(int $id, string $newStatus): void
    {
        if (!in_array($newStatus, self::ALLOWED, true)) {
            throw new \InvalidArgumentException("Statut invalide : $newStatus");
        }

        $reservation = $this->repo->findById($id);
        if (!$reservation) {
            throw new \RuntimeException('Réservation introuvable.');
        }

        $reservation->setStatus($newStatus);
        $reservation->setUpdatedAt(new \DateTimeImmutable());
        $this->repo->update($reservation);
    }
}
