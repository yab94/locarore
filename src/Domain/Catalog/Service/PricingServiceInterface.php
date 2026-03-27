<?php

declare(strict_types=1);

namespace Rore\Domain\Catalog\Service;

use Rore\Domain\Catalog\Entity\Pack;
use Rore\Domain\Catalog\Entity\PricableInterface;
use Rore\Domain\Catalog\Entity\Product;

interface PricingServiceInterface
{
    public function calculate(
        PricableInterface         $item,
        \DateTimeImmutable|string $start,
        \DateTimeImmutable|string $end,
    ): float;

    /** @param Product[] $products */
    public function calculateItemsTotal(
        Pack                      $pack,
        array                     $products,
        \DateTimeImmutable|string $start,
        \DateTimeImmutable|string $end,
    ): float;
}
