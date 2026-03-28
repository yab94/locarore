<?php

declare(strict_types=1);

namespace Rore\Application\Cart\UseCase;

use Rore\Application\Cart\Port\CartServiceInterface;
use Rore\Application\Cart\Service\CartService;
use RRB\Di\BindAdapter;

class ClearCartUseCase
{
    public function __construct(
        #[BindAdapter(CartService::class)]
        private readonly CartServiceInterface $cart,
    ) {
    }

    public function execute(): void
    {
        $this->cart->clear();
    }
}
