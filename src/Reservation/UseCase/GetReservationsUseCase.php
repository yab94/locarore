<?php

declare(strict_types=1);

namespace Rore\Reservation\UseCase;

use Rore\Reservation\Port\ReservationRepositoryInterface;
use Rore\Reservation\Adapter\MySqlReservationRepository;
use Rore\Framework\Di\BindAdapter;

class GetReservationsUseCase
{
    public function __construct(
        #[BindAdapter(MySqlReservationRepository::class)]
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
