<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Catalog\UseCase\CreatePackUseCase;
use Rore\Application\Catalog\UseCase\GetAllCategoriesUseCase;
use Rore\Application\Catalog\UseCase\GetAllPacksWithPricingUseCase;
use Rore\Application\Catalog\UseCase\GetAllProductsUseCase;
use Rore\Application\Catalog\UseCase\GetPackByIdUseCase;
use Rore\Application\Catalog\UseCase\UpdatePackUseCase;
use Rore\Application\Catalog\UseCase\TogglePackUseCase;

use Rore\Framework\Http\Route;
class PackController extends AdminController
{
    public function __construct(
        private readonly GetAllPacksWithPricingUseCase $getAllPacksWithPricingUseCase,
        private readonly GetAllProductsUseCase         $getAllProductsUseCase,
        private readonly GetAllCategoriesUseCase       $getAllCategoriesUseCase,
        private readonly GetPackByIdUseCase            $getPackByIdUseCase,
        private readonly CreatePackUseCase      $createPackUseCase,
        private readonly UpdatePackUseCase      $updatePackUseCase,
        private readonly TogglePackUseCase      $togglePackUseCase,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    #[Route('GET', '/admin/packs')]
    public function index(): void
    {
        $data = $this->getAllPacksWithPricingUseCase->execute();

        $this->render('admin/packs/list', [
            'title'            => 'Packs',
            'packs'            => $data['packs'],
            'products'         => $data['products'],
            'categories'       => $this->getAllCategoriesUseCase->execute(),
            'packRetailPrices' => $data['retailPrices'],
        ]);
    }

    #[Route('GET', '/admin/packs/creer')]
    public function create(): void
    {
        $this->render('admin/packs/form', [
            'title'      => 'Nouveau pack',
            'pack'       => null,
            'products'   => $this->getAllProductsUseCase->execute(),
            'categories' => $this->getAllCategoriesUseCase->execute(),
        ]);
    }

    #[Route('POST', '/admin/packs/creer')]
    public function store(): void
    {
        $this->requirePost();
        try {
            $this->createPackUseCase->execute(
                name:               $this->request->body->getString('name'),
                description:        $this->request->body->getString('description') ?: null,
                descriptionShort:   $this->request->body->getString('description_short') ?: null,
                pricePerDay:        $this->request->body->getFloat('price_per_day'),
                priceExtraWeekend:  $this->request->body->getFloat('price_extra_weekend'),
                priceExtraWeekday:  $this->request->body->getFloat('price_extra_weekday'),
                items:              $this->parseItems(),
                slots:              $this->parseSlots(),
                customSlug:         $this->request->body->getString('slug') ?: null,
            );
            $this->flash('success', 'Pack créé.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }

    #[Route('GET', '/admin/packs/{id}/modifier')]
    public function edit(string $id): void
    {
        $pack = $this->getPackByIdUseCase->execute((int) $id);
        if (!$pack) {
            $this->redirect($this->urlResolver->resolve(self::class . '.index'));
        }
        $this->render('admin/packs/form', [
            'title'      => 'Modifier le pack',
            'pack'       => $pack,
            'products'   => $this->getAllProductsUseCase->execute(),
            'categories' => $this->getAllCategoriesUseCase->execute(),
        ]);
    }

    #[Route('POST', '/admin/packs/{id}/modifier')]
    public function update(string $id): void
    {
        $this->requirePost();
        try {
            $this->updatePackUseCase->execute(
                id:                 (int) $id,
                name:               $this->request->body->getString('name'),
                description:        $this->request->body->getString('description') ?: null,
                descriptionShort:   $this->request->body->getString('description_short') ?: null,
                pricePerDay:        $this->request->body->getFloat('price_per_day'),
                priceExtraWeekend:  $this->request->body->getFloat('price_extra_weekend'),
                priceExtraWeekday:  $this->request->body->getFloat('price_extra_weekday'),
                items:              $this->parseItems(),
                slots:              $this->parseSlots(),
                customSlug:         $this->request->body->getString('slug') ?: null,
            );
            $this->flash('success', 'Pack mis à jour.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }

    #[Route('POST', '/admin/packs/{id}/toggle')]
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
        $productIds = $this->request->body->getArray('item_product_id');
        $quantities = $this->request->body->getArray('item_quantity');
        foreach ($productIds as $i => $pid) {
            $pid = (int) $pid;
            $qty = (int) ($quantities[$i] ?? 0);
            if ($pid > 0 && $qty > 0) {
                $items[$pid] = $qty;
            }
        }
        return $items;
    }

    /** @return array<int,int> [categoryId => quantity] */
    private function parseSlots(): array
    {
        $slots = [];
        $categoryIds = $this->request->body->getArray('slot_category_id');
        $quantities  = $this->request->body->getArray('slot_quantity');
        foreach ($categoryIds as $i => $cid) {
            $cid = (int) $cid;
            $qty = (int) ($quantities[$i] ?? 0);
            if ($cid > 0 && $qty > 0) {
                $slots[$cid] = $qty;
            }
        }
        return $slots;
    }
}
