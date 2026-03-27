<?php

declare(strict_types=1);

namespace Rore\Application\Cart\UseCase;

use Rore\Domain\Cart\Service\CartService;

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
