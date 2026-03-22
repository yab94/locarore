<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Catalog\CreatePackUseCase;
use Rore\Application\Catalog\UpdatePackUseCase;
use Rore\Application\Catalog\TogglePackUseCase;
use Rore\Infrastructure\Persistence\MySqlPackRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;

class PackController extends AdminController
{
    private MySqlPackRepository    $packRepo;
    private MySqlProductRepository $productRepo;

    public function __construct()
    {
        parent::__construct();
        $this->packRepo    = new MySqlPackRepository();
        $this->productRepo = new MySqlProductRepository();
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
            (new CreatePackUseCase($this->packRepo))->execute(
                name:        trim($_POST['name'] ?? ''),
                description: trim($_POST['description'] ?? '') ?: null,
                pricePerDay: (float) ($_POST['price_per_day'] ?? 0),
                items:       $items,
                customSlug:  trim($_POST['slug'] ?? '') ?: null,
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
            (new UpdatePackUseCase($this->packRepo))->execute(
                id:          (int) $id,
                name:        trim($_POST['name'] ?? ''),
                description: trim($_POST['description'] ?? '') ?: null,
                pricePerDay: (float) ($_POST['price_per_day'] ?? 0),
                items:       $items,
                customSlug:  trim($_POST['slug'] ?? '') ?: null,
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
            (new TogglePackUseCase($this->packRepo))->execute((int) $id);
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect('/admin/packs');
    }

    /** @return array<int,int> [productId => quantity] */
    private function parseItems(): array
    {
        $items = [];
        $productIds = $_POST['item_product_id'] ?? [];
        $quantities = $_POST['item_quantity'] ?? [];
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
