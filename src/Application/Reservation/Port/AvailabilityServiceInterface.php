<?php

declare(strict_types=1);

namespace Rore\Application\Reservation\Port;

use Rore\Domain\Catalog\Entity\Pack;
use Rore\Domain\Catalog\Entity\Product;

interface AvailabilityServiceInterface
{
    public function getAvailableQuantity(
        Product             $product,
        \DateTimeImmutable  $start,
        \DateTimeImmutable  $end,
        ?\DateTimeImmutable $now = null,
    ): int;

    public function isAvailable(
        Product             $product,
        int                 $requestedQty,
        \DateTimeImmutable  $start,
        \DateTimeImmutable  $end,
        ?\DateTimeImmutable $now = null,
    ): bool;

    /** @param array<int, Product> $productsById */
    public function isPackAvailable(
        Pack                $pack,
        array               $productsById,
        \DateTimeImmutable  $start,
        \DateTimeImmutable  $end,
        ?\DateTimeImmutable $now = null,
    ): bool;
}
