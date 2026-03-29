<?php

declare(strict_types=1);

namespace Rore\Application\Cart\UseCase;

use Rore\Application\Cart\Port\CartServiceInterface;
use Rore\Application\Cart\Service\CartService;

class RemoveFromCartUseCase
{
    public function __construct(
        private CartServiceInterface $cart,
    ) {}

    public function execute(int $productId): void
    {
        $this->cart->removeItem($productId);
    }
}
