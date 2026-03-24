<?php

declare(strict_types=1);

namespace Rore\Application\Cart;

class RemovePackFromCartUseCase
{
    public function __construct(
        private CartSession $cart,
    ) {}

    public function execute(int $packId): void
    {
        $this->cart->removePack($packId);
    }
}
