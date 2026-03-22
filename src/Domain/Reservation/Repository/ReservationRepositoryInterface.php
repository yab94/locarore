<?php

declare(strict_types=1);

namespace Rore\Domain\Reservation\Repository;

use Rore\Domain\Reservation\Entity\Reservation;

interface ReservationRepositoryInterface
{
    /** @return Reservation[] */
    public function findAll(): array;

    public function findById(int $id): ?Reservation;

    /** @return Reservation[] */
    public function findByStatus(string $status): array;

    /**
     * Retourne toutes les réservations confirmées dont la plage
     * chevauche [$start, $end].
     *
     * @return Reservation[]
     */
    public function findConfirmedOverlapping(
        \DateTimeImmutable $start,
        \DateTimeImmutable $end
    ): array;

    /**
     * Insère une nouvelle réservation et retourne son id.
     */
    public function save(Reservation $reservation): int;

    public function update(Reservation $reservation): void;
}
