<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Catalog\CreatePackUseCase;
use Rore\Application\Catalog\UpdatePackUseCase;
use Rore\Application\Catalog\TogglePackUseCase;
use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Infrastructure\Config\SettingsStore;
use Rore\Infrastructure\Persistence\MySqlPackRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Presentation\Http\RequestInterface;
use Rore\Presentation\Http\ResponseInterface;

class PackController extends AdminController
{
    public function __construct(
        private readonly MySqlPackRepository    $packRepo,
        private readonly MySqlProductRepository $productRepo,
        private readonly CreatePackUseCase      $createPackUseCase,
        private readonly UpdatePackUseCase      $updatePackUseCase,
        private readonly TogglePackUseCase      $togglePackUseCase,
        RequestInterface                        $request,
        ResponseInterface                       $response,
        SessionStorageInterface                 $session,
        CsrfTokenManagerInterface               $csrfTokenManager,
        SettingsStore                           $settings,
    ) {
        parent::__construct($request, $response, $session, $csrfTokenManager, $settings);
    }

    public function index(): void
    {
        $this->render('admin/packs/list', [
            'title' => 'Packs',
            'packs' => $this->packRepo->findAll(),
        ]);
    }

    public function create(): void
    {
        $this->render('admin/packs/form', [
            'title'    => 'Nouveau pack',
            'pack'     => null,
            'products' => $this->productRepo->findAll(),
        ]);
    }

    public function store(): void
    {
        $this->requirePost();
        try {
            $items = $this->parseItems();
            $this->createPackUseCase->execute(
                name:        $this->inputString('name'),
                description: $this->inputStringOrNull('description'),
                pricePerDay: $this->inputFloat('price_per_day'),
                items:       $items,
                customSlug:  $this->inputStringOrNull('slug'),
            );
            $this->flash('success', 'Pack créé.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect('/admin/packs');
    }

    public function edit(string $id): void
    {
        $pack = $this->packRepo->findById((int) $id);
        if (!$pack) {
            $this->redirect('/admin/packs');
        }
        $this->render('admin/packs/form', [
            'title'    => 'Modifier le pack',
            'pack'     => $pack,
            'products' => $this->productRepo->findAll(),
        ]);
    }

    public function update(string $id): void
    {
        $this->requirePost();
        try {
            $items = $this->parseItems();
            $this->updatePackUseCase->execute(
                id:          (int) $id,
                name:        $this->inputString('name'),
                description: $this->inputStringOrNull('description'),
                pricePerDay: $this->inputFloat('price_per_day'),
                items:       $items,
                customSlug:  $this->inputStringOrNull('slug'),
            );
            $this->flash('success', 'Pack mis à jour.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect('/admin/packs');
    }

    public function toggle(string $id): void
    {
        $this->requirePost();
        try {
            $this->togglePackUseCase->execute((int) $id);
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect('/admin/packs');
    }

    /** @return array<int,int> [productId => quantity] */
    private function parseItems(): array
    {
        $items = [];
        $productIds = $this->inputArray('item_product_id');
        $quantities = $this->inputArray('item_quantity');
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
