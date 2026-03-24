<?php

declare(strict_types=1);

namespace Rore\Application\Cart;

use Rore\Domain\Catalog\Repository\PackRepositoryInterface;

class AddPackToCartUseCase
{
    public function __construct(
        private CartSession            $cart,
        private PackRepositoryInterface $packRepository,
    ) {}

    public function execute(int $packId): void
    {
        if (!$this->cart->hasDates()) {
            throw new \RuntimeException("Veuillez d'abord choisir vos dates.");
        }

        $pack = $this->packRepository->findById($packId);
        if ($pack === null || !$pack->isActive()) {
            throw new \RuntimeException("Ce pack n'est pas disponible.");
        }

        $this->cart->addPack($packId);
    }
}
