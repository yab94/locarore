<?php

declare(strict_types=1);

namespace Rore\Application\Reservation\UseCase;

use Rore\Domain\Reservation\Repository\ReservationRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlReservationRepository;
use RRB\Di\BindAdapter;

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
