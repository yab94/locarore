<?php

declare(strict_types=1);

namespace Rore\Application\Cart;

class RemoveFromCartUseCase
{
    public function __construct(
        private CartSession $cart,
    ) {}

    public function execute(int $productId): void
    {
        $this->cart->removeItem($productId);
    }
}
