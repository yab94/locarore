<?php

declare(strict_types=1);

namespace Rore\Application\Cart\UseCase;

use Rore\Application\Cart\Port\CartServiceInterface;
use Rore\Application\Cart\Service\CartService;
use Rore\Application\Catalog\Port\PackRepositoryInterface;
use Rore\Application\Catalog\Port\ProductRepositoryInterface;
use Rore\Application\Reservation\Service\AvailabilityService;
use Rore\Application\Reservation\Port\AvailabilityServiceInterface;
use Rore\Infrastructure\Persistence\MySqlProductRepositoryAdapter;
use Rore\Infrastructure\Persistence\MySqlPackRepositoryAdapter;
use RRB\Di\BindAdapter;

class AddPackToCartUseCase
{
    public function __construct(
        #[BindAdapter(CartService::class)]
        private CartServiceInterface                 $cart,
        #[BindAdapter(MySqlPackRepositoryAdapter::class)]
        private PackRepositoryInterface $packRepository,
        #[BindAdapter(MySqlProductRepositoryAdapter::class)]
        private ProductRepositoryInterface $productRepository,
        #[BindAdapter(AvailabilityService::class)]
        private AvailabilityServiceInterface   $availabilityService,
    ) {}

    public function execute(int $packId, array $selections = []): void
    {
        if (!$this->cart->hasDates()) {
            throw new \RuntimeException("Veuillez d'abord choisir vos dates.");
        }

        $pack = $this->packRepository->findById($packId);
        if ($pack === null || !$pack->isActive()) {
            throw new \RuntimeException("Ce pack n'est pas disponible.");
        }

        // Charger tous les produits fixes du pack
        $productsById = [];
        foreach ($pack->getItems() as $item) {
            if (!$item->isFixed()) continue;
            $product = $this->productRepository->findById($item->getProductId());
            if ($product) {
                $productsById[$product->getId()] = $product;
            }
        }

        // Ajouter les produits choisis pour les slots
        foreach ($selections as $slotItemId => $productId) {
            $product = $this->productRepository->findById((int) $productId);
            if ($product) {
                $productsById[$product->getId()] = $product;
            }
        }

        $start = new \DateTimeImmutable($this->cart->getStartDate());
        $end   = new \DateTimeImmutable($this->cart->getEndDate());

        // Vérifier que tous les produits du pack sont disponibles
        if (!$this->availabilityService->isPackAvailable($pack, $productsById, $start, $end)) {
            throw new \RuntimeException(
                "Un ou plusieurs produits de ce pack ne sont pas disponibles sur cette période."
            );
        }

        $this->cart->addPack($packId);

        // Stocker les sélections de slots
        foreach ($selections as $slotItemId => $productId) {
            if ((int) $productId > 0) {
                $this->cart->setPackSelection($packId, (int) $slotItemId, (int) $productId);
            }
        }
    }
}
