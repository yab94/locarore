<?php

declare(strict_types=1);

namespace Rore\Cart\UseCase;

use Rore\Cart\Adapter\CartService;
use Rore\Cart\Port\CartServiceInterface;
use Rore\Framework\Di\BindAdapter;

class RemoveFromCartUseCase
{
    public function __construct(
        #[BindAdapter(CartService::class)]
        private CartServiceInterface $cart,
    ) {}

    public function execute(int $productId): void
    {
        $this->cart->removeItem($productId);
    }
}
