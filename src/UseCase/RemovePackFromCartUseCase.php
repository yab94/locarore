<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Service\CartService;

class RemovePackFromCartUseCase
{
    public function __construct(
        private CartService $cart,
    ) {}

    public function execute(int $packId): void
    {
        $this->cart->removePack($packId);
    }
}
