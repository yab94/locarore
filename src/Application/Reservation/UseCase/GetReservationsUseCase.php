<?php

declare(strict_types=1);

namespace Rore\Application\Reservation\UseCase;

use Rore\Application\Reservation\Port\ReservationRepositoryInterface;
use Rore\Domain\Reservation\ValueObject\ReservationStatus;

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
        return $this->reservationRepository->findByStatus(ReservationStatus::from($status));
    }

    public function pending(): array
    {
        return $this->reservationRepository->findByStatus(ReservationStatus::Pending);
    }
}
