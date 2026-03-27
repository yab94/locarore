<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Service\CartService;

class RemoveFromCartUseCase
{
    public function __construct(
        private CartService $cart,
    ) {}

    public function execute(int $productId): void
    {
        $this->cart->removeItem($productId);
    }
}
