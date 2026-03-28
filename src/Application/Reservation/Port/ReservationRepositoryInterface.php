<?php

declare(strict_types=1);

namespace Rore\Application\Reservation\Port;

use Rore\Domain\Reservation\Entity\Reservation;
use Rore\Domain\Reservation\ValueObject\ReservationStatus;

interface ReservationRepositoryInterface
{
    /** @return Reservation[] */
    public function findAll(): array;

    public function findById(int $id): ?Reservation;

    /** @return Reservation[] */
    public function findByStatus(ReservationStatus $status): array;

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
     * Compte la quantité totale réservée pour un produit sur une période.
     */
    public function countReservedQtyForProduct(int $productId, string $startDate, string $endDate): int;

    /**
     * Retourne les périodes réservées pour un produit (pour le calendrier admin).
     */
    public function getReservedPeriodsByProduct(int $productId): array;

    /**
     * Insère une nouvelle réservation et retourne son id.
     */
    public function save(Reservation $reservation): int;

    public function update(Reservation $reservation): void;
}
