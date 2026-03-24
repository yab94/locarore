<?php

declare(strict_types=1);

namespace Rore\Domain\Catalog\Entity;

/**
 * Contrat commun aux entités tarifables (Product, Pack).
 * Permet au PricingService de calculer un prix sans connaître
 * le type concret de l'entité.
 */
interface PricableInterface
{
    /**
     * Forfait de base couvrant les 2 premiers jours.
     * (priceBase pour Product, pricePerDay pour Pack)
     */
    public function getBasePrice(): float;

    /** Supplément par jour au-delà de 2j pour un WE (sam+dim, ≤ 4j). */
    public function getPriceExtraWeekend(): float;

    /** Supplément par jour au-delà de 2j sinon. */
    public function getPriceExtraWeekday(): float;
}
