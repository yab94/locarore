<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Catalog\CreatePackUseCase;
use Rore\Application\Catalog\GetAllPacksWithPricingUseCase;
use Rore\Application\Catalog\GetAllProductsUseCase;
use Rore\Application\Catalog\GetPackByIdUseCase;
use Rore\Application\Catalog\UpdatePackUseCase;
use Rore\Application\Catalog\TogglePackUseCase;

class PackController extends AdminController
{
    public function __construct(
        private readonly GetAllPacksWithPricingUseCase $getAllPacksWithPricingUseCase,
        private readonly GetAllProductsUseCase         $getAllProductsUseCase,
        private readonly GetPackByIdUseCase            $getPackByIdUseCase,
        private readonly CreatePackUseCase      $createPackUseCase,
        private readonly UpdatePackUseCase      $updatePackUseCase,
        private readonly TogglePackUseCase      $togglePackUseCase,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    public function index(): void
    {
        $data = $this->getAllPacksWithPricingUseCase->execute();

        $this->render('admin/packs/list', [
            'title'            => 'Packs',
            'packs'            => $data['packs'],
            'products'         => $data['products'],
            'packRetailPrices' => $data['retailPrices'],
        ]);
    }

    public function create(): void
    {
        $this->render('admin/packs/form', [
            'title'    => 'Nouveau pack',
            'pack'     => null,
            'products' => $this->getAllProductsUseCase->execute(),
        ]);
    }

    public function store(): void
    {
        $this->requirePost();
        try {
            $items = $this->parseItems();
            $this->createPackUseCase->execute(
                name:               $this->request->body->getStringParam('name'),
                description:        $this->request->body->getStringParam('description') ?: null,
                pricePerDay:        $this->request->body->getFloatParam('price_per_day'),
                priceExtraWeekend:  $this->request->body->getFloatParam('price_extra_weekend'),
                priceExtraWeekday:  $this->request->body->getFloatParam('price_extra_weekday'),
                items:              $items,
                customSlug:         $this->request->body->getStringParam('slug') ?: null,
            );
            $this->flash('success', 'Pack créé.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }

    public function edit(string $id): void
    {
        $pack = $this->getPackByIdUseCase->execute((int) $id);
        if (!$pack) {
            $this->redirect($this->urlResolver->resolve(self::class . '.index'));
        }
        $this->render('admin/packs/form', [
            'title'    => 'Modifier le pack',
            'pack'     => $pack,
            'products' => $this->getAllProductsUseCase->execute(),
        ]);
    }

    public function update(string $id): void
    {
        $this->requirePost();
        try {
            $items = $this->parseItems();
            $this->updatePackUseCase->execute(
                id:                 (int) $id,
                name:               $this->request->body->getStringParam('name'),
                description:        $this->request->body->getStringParam('description') ?: null,
                pricePerDay:        $this->request->body->getFloatParam('price_per_day'),
                priceExtraWeekend:  $this->request->body->getFloatParam('price_extra_weekend'),
                priceExtraWeekday:  $this->request->body->getFloatParam('price_extra_weekday'),
                items:              $items,
                customSlug:         $this->request->body->getStringParam('slug') ?: null,
            );
            $this->flash('success', 'Pack mis à jour.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }

    public function toggle(string $id): void
    {
        $this->requirePost();
        try {
            $this->togglePackUseCase->execute((int) $id);
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }

    /** @return array<int,int> [productId => quantity] */
    private function parseItems(): array
    {
        $items = [];
        $productIds = $this->request->body->getArrayParam('item_product_id');
        $quantities = $this->request->body->getArrayParam('item_quantity');
        foreach ($productIds as $i => $pid) {
            $pid = (int) $pid;
            $qty = (int) ($quantities[$i] ?? 0);
            if ($pid > 0 && $qty > 0) {
                $items[$pid] = $qty;
            }
        }
        return $items;
    }
}
