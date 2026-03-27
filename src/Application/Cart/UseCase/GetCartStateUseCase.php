<?php

declare(strict_types=1);

namespace Rore\Application\Cart\UseCase;

use Rore\Domain\Cart\ValueObject\CartState;
use Rore\Application\Cart\Service\CartService;

/**
 * Retourne un snapshot read-only de l'état du panier.
 * Utilisé par SiteController pour alimenter toutes les vues.
 */
final class GetCartStateUseCase
{
    public function __construct(
        private readonly CartService $cart,
    ) {}

    public function execute(): CartState
    {
        return new CartState(
            hasDates:   $this->cart->hasDates(),
            startDate:  $this->cart->getStartDate(),
            endDate:    $this->cart->getEndDate(),
            isEmpty:    $this->cart->isEmpty(),
            itemCount:  $this->cart->getItemCount(),
            items:      $this->cart->getItems(),
            packs:      $this->cart->getPacks(),
        );
    }
}
