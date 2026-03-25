<?php

declare(strict_types=1);

namespace Rore\Domain\Catalog\Service;

use Rore\Domain\Catalog\Entity\PricableInterface;
use Rore\Domain\Catalog\Entity\Pack;
use Rore\Domain\Catalog\Entity\Product;

/**
 * Service de domaine : calcul du prix d'une entité tarifable pour une période.
 *
 * Règles :
 * - Le forfait de base (getBasePrice) couvre les 2 premiers jours.
 * - Week-end (sam+dim présents ET ≤ 4 jours) → supplément getPriceExtraWeekend() €/j au-delà de 2j.
 * - Sinon → supplément getPriceExtraWeekday() €/j au-delà de 2j.
 */
final class PricingService
{
    public function calculate(
        PricableInterface             $item,
        \DateTimeImmutable|string     $start,
        \DateTimeImmutable|string     $end,
    ): float {
        if (is_string($start)) $start = new \DateTimeImmutable($start);
        if (is_string($end))   $end   = new \DateTimeImmutable($end);

        $days = max(1, (int) $start->diff($end)->days + 1);

        $hasSat = false;
        $hasSun = false;
        $cur    = $start;
        while ($cur <= $end) {
            $dow = (int) $cur->format('N'); // 1=Lun … 7=Dim
            if ($dow === 6) $hasSat = true;
            if ($dow === 7) $hasSun = true;
            $cur = $cur->modify('+1 day');
        }

        $isWeekend = $hasSat && $hasSun && $days <= 4;
        $extraRate = $isWeekend ? $item->getPriceExtraWeekend() : $item->getPriceExtraWeekday();
        $extraDays = max(0, $days - 2);

        return $item->getBasePrice() + ($extraDays * $extraRate);
    }

    /**
     * Prix théorique des articles d'un pack au détail (quantité × prix unitaire).
     * Permet d'afficher la « valeur » et l'économie réalisée.
     *
     * @param Product[] $products Tous les produits du pack (dans n'importe quel ordre)
     */
    public function calculateItemsTotal(
        Pack                      $pack,
        array                     $products,
        \DateTimeImmutable|string $start,
        \DateTimeImmutable|string $end,
    ): float {
        $byId = [];
        foreach ($products as $product) {
            $byId[$product->getId()] = $product;
        }

        $total = 0.0;
        foreach ($pack->getItems() as $item) {
            if (!$item->isFixed()) {
                continue;
            }
            $product = $byId[$item->getProductId()] ?? null;
            if ($product !== null) {
                $total += $item->getQuantity() * $this->calculate($product, $start, $end);
            }
        }

        return $total;
    }
}
