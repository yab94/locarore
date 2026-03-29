<?php

declare(strict_types=1);

namespace Rore\Application\Reservation\Service;

use Rore\Domain\Catalog\Entity\Product;
use Rore\Application\Reservation\Port\ReservationRepositoryInterface;
use Rore\Application\Reservation\Port\AvailabilityServiceInterface;

/**
 * Calcule la disponibilité d'un produit sur une plage de dates.
 * Seules les réservations au statut "confirmed" consomment du stock.
 */
class AvailabilityService implements AvailabilityServiceInterface
{
    public function __construct(
        private ReservationRepositoryInterface $reservationRepository,
    ) {}

    private function getConsumedQuantity(
        int $productId,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
    ): int {
        $reservations = $this->reservationRepository->findConfirmedOverlapping($start, $end);

        $consumed = 0;
        foreach ($reservations as $reservation) {
            foreach ($reservation->getItems() as $item) {
                if ($item->getProductId() === $productId) {
                    $consumed += $item->getQuantity();
                }
            }
        }

        return $consumed;
    }

    public function getAvailableQuantity(
        Product            $product,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
        ?\DateTimeImmutable $now = null,
    ): int {
        $productId = (int) ($product->getId() ?? 0);

        $consumed = $this->getConsumedQuantity($productId, $start, $end);

        $availableDurable = max(0, $product->getStock() - $consumed);
        $consumedAfterDurable = max(0, $consumed - $product->getStock());
        $remainingOnDemand = max(0, $product->getStockOnDemand() - $consumedAfterDurable);

        if ($remainingOnDemand <= 0) {
            return $availableDurable;
        }

        $unitDays = $product->getFabricationTimeDays();
        if ($unitDays <= 0) {
            return $availableDurable + $remainingOnDemand;
        }

        $now = $now ?? new \DateTimeImmutable();
        $secondsRemaining = max(0, $start->getTimestamp() - $now->getTimestamp());
        $unitSeconds = (float) ($unitDays * 86400);
        if ($unitSeconds <= 0) {
            return $availableDurable + $remainingOnDemand;
        }

        $maxBuildable = (int) floor($secondsRemaining / $unitSeconds);
        $usableOnDemand = min($remainingOnDemand, $maxBuildable);

        return $availableDurable + $usableOnDemand;
    }

    public function isAvailable(
        Product            $product,
        int                $requestedQty,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
        ?\DateTimeImmutable $now = null,
    ): bool {
        if ($requestedQty < 1) {
            return true;
        }
        $productId = (int) ($product->getId() ?? 0);

        $consumed = $this->getConsumedQuantity($productId, $start, $end);

        $availableDurable = max(0, $product->getStock() - $consumed);
        $consumedAfterDurable = max(0, $consumed - $product->getStock());
        $remainingOnDemand = max(0, $product->getStockOnDemand() - $consumedAfterDurable);

        $availableTotal = $availableDurable + $remainingOnDemand;
        if ($availableTotal < $requestedQty) {
            return false;
        }

        // Si la demande est couverte par le stock durable, pas de délai de fabrication.
        if ($requestedQty <= $availableDurable) {
            return true;
        }

        $onDemandNeeded = $requestedQty - $availableDurable;
        if ($onDemandNeeded > $remainingOnDemand) {
            return false;
        }

        $unitDays = $product->getFabricationTimeDays();
        if ($unitDays <= 0) {
            return true;
        }

        $now = $now ?? new \DateTimeImmutable();
        $secondsRemaining = $start->getTimestamp() - $now->getTimestamp();
        if ($secondsRemaining <= 0) {
            return false;
        }

        $requiredSeconds = (float) $onDemandNeeded * ($unitDays * 86400);
        return $secondsRemaining >= $requiredSeconds;
    }

    /**
     * Vérifie si tous les produits d'un pack sont disponibles pour les dates demandées.
     * 
     * @param \Rore\Domain\Catalog\Entity\Pack $pack
     * @param Product[] $productsById  Tableau indexé par product_id
     * @param \DateTimeImmutable $start
     * @param \DateTimeImmutable $end
     * @param \DateTimeImmutable|null $now
     * @return bool
     */
    public function isPackAvailable(
        \Rore\Domain\Catalog\Entity\Pack $pack,
        array $productsById,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
        ?\DateTimeImmutable $now = null,
    ): bool {
        foreach ($pack->getItems() as $item) {
            $product = $productsById[$item->getProductId()] ?? null;
            if ($product === null || !$product->isActive()) {
                return false;
            }
            
            if (!$this->isAvailable($product, $item->getQuantity(), $start, $end, $now)) {
                return false;
            }
        }
        
        return true;
    }
}
