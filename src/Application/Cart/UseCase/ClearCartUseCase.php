<?php

declare(strict_types=1);

namespace Rore\Application\Cart\UseCase;

use Rore\Application\Cart\Port\CartServiceInterface;
use Rore\Application\Cart\Service\CartService;

class ClearCartUseCase
{
    public function __construct(
        private readonly CartServiceInterface $cart,
    ) {
    }

    public function execute(): void
    {
        $this->cart->clear();
    }
}
