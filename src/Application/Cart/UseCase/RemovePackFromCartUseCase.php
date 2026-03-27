<?php

declare(strict_types=1);

namespace Rore\Application\Cart\UseCase;

use Rore\Application\Cart\Port\CartServiceInterface;
use Rore\Application\Cart\Service\CartService;
use RRB\Di\BindAdapter;

class RemovePackFromCartUseCase
{
    public function __construct(
        #[BindAdapter(CartService::class)]
        private CartServiceInterface $cart,
    ) {}

    public function execute(int $packId): void
    {
        $this->cart->removePack($packId);
    }
}
