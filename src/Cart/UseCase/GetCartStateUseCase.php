<?php

declare(strict_types=1);

namespace Rore\Cart\UseCase;

use Rore\Cart\Adapter\CartService;
use Rore\Cart\ValueObject\CartState;
use Rore\Cart\Port\CartServiceInterface;
use Rore\Framework\Di\BindAdapter;

/**
 * Retourne un snapshot read-only de l'état du panier.
 * Utilisé par SiteController pour alimenter toutes les vues.
 */
final class GetCartStateUseCase
{
    public function __construct(
        #[BindAdapter(CartService::class)]
        private readonly CartServiceInterface $cart,
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
