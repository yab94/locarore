<?php

declare(strict_types=1);

namespace Rore\Cart\UseCase;

use Rore\Cart\Adapter\CartService;
use Rore\Cart\Port\CartServiceInterface;
use Rore\Framework\Di\BindAdapter;

class SetCartDatesUseCase
{
    public function __construct(
        #[BindAdapter(CartService::class)]
        private CartServiceInterface $cart,
    ) {}

    public function execute(string $startDate, string $endDate): void
    {
        $start = new \DateTimeImmutable($startDate);
        $end   = new \DateTimeImmutable($endDate);

        if ($end < $start) {
            throw new \InvalidArgumentException("La date de fin doit être après la date de début.");
        }
        if ($start < new \DateTimeImmutable('today')) {
            throw new \InvalidArgumentException("La date de début ne peut pas être dans le passé.");
        }

        $this->cart->setDates(
            $start->format('Y-m-d'),
            $end->format('Y-m-d'),
        );
    }
}
