<?php

declare(strict_types=1);

namespace Rore\Application\Reservation;

use Rore\Domain\Reservation\Repository\ReservationRepositoryInterface;

class GetReservationsUseCase
{
    public function __construct(
        private ReservationRepositoryInterface $reservationRepository,
    ) {}

    public function all(): array
    {
        return $this->reservationRepository->findAll();
    }

    public function byStatus(string $status): array
    {
        return $this->reservationRepository->findByStatus($status);
    }

    public function pending(): array
    {
        return $this->reservationRepository->findByStatus('pending');
    }
}
