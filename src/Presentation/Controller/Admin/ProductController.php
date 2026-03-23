<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Catalog\CreateProductUseCase;
use Rore\Application\Catalog\DeleteProductPhotoUseCase;
use Rore\Application\Catalog\ToggleProductUseCase;
use Rore\Application\Catalog\UpdateProductUseCase;
use Rore\Application\Catalog\UploadProductPhotoUseCase;
use Rore\Domain\Catalog\Service\SlugUniquenessChecker;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Infrastructure\Persistence\MySqlPackRepository;
use Rore\Infrastructure\Persistence\MySqlReservationRepository;
use Rore\Infrastructure\Storage\FileUploader;

class ProductController extends AdminController
{
    public function __construct(
        private readonly MySqlProductRepository     $productRepo,
        private readonly MySqlCategoryRepository    $categoryRepo,
        private readonly MySqlReservationRepository $reservationRepo,
        private readonly SlugUniquenessChecker      $slugChecker,
        private readonly FileUploader               $uploader,
    ) {
        parent::__construct();
    }

    public function index(): void
    {
        $this->render('admin/products/list', [
            'title'    => 'Produits',
            'products' => $this->productRepo->findAll(),
        ]);
    }

    public function create(): void
    {
        $this->render('admin/products/form', [
            'title'      => 'Nouveau produit',
            'product'    => null,
            'categories' => $this->categoryRepo->findAll(),
        ]);
    }

    public function store(): void
    {
        $this->requirePost();
        try {
            $productId = (new CreateProductUseCase($this->productRepo, $this->slugChecker))->execute(
                categoryId:       (int) ($_POST['category_id'] ?? 0),
                name:             trim($_POST['name'] ?? ''),
                description:      trim($_POST['description'] ?? '') ?: null,
                stock:            (int) ($_POST['stock'] ?? 0),
                priceBase:        (float) ($_POST['price_base'] ?? 80),
                stockOnDemand:    (int) ($_POST['stock_on_demand'] ?? 0),
                priceExtraWeekend: (float) ($_POST['price_extra_weekend'] ?? 0),
                priceExtraWeekday: (float) ($_POST['price_extra_weekday'] ?? 15),
                extraCategoryIds: array_map('intval', $_POST['extra_category_ids'] ?? []),
                customSlug:       trim($_POST['slug'] ?? '') ?: null,
            );

            if (!empty($_FILES['photos']['name'][0])) {
                $this->handlePhotoUploads($productId, $_FILES['photos']);
            }

            $this->flash('success', 'Produit créé avec succès.');
            $this->redirect('/admin/produits/' . $productId . '/modifier');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect('/admin/produits/creer');
        }
    }

    public function edit(string $id): void
    {
        $product = $this->productRepo->findById((int) $id);
        if (!$product) {
            $this->redirect('/admin/produits');
        }
        $calendarEvents = $this->reservationRepo->getReservedPeriodsByProduct($product->getId());

        $this->render('admin/products/form', [
            'title'          => 'Modifier le produit',
            'product'        => $product,
            'categories'     => $this->categoryRepo->findAll(),
            'calendarEvents' => $calendarEvents,
        ]);
    }

    public function update(string $id): void
    {
        $this->requirePost();
        try {
            (new UpdateProductUseCase($this->productRepo, $this->slugChecker))->execute(
                id:               (int) $id,
                categoryId:       (int) ($_POST['category_id'] ?? 0),
                name:             trim($_POST['name'] ?? ''),
                description:      trim($_POST['description'] ?? '') ?: null,
                stock:            (int) ($_POST['stock'] ?? 0),
                priceBase:        (float) ($_POST['price_base'] ?? 80),
                stockOnDemand:    (int) ($_POST['stock_on_demand'] ?? 0),
                priceExtraWeekend: (float) ($_POST['price_extra_weekend'] ?? 0),
                priceExtraWeekday: (float) ($_POST['price_extra_weekday'] ?? 15),
                extraCategoryIds: array_map('intval', $_POST['extra_category_ids'] ?? []),
                customSlug:       trim($_POST['slug'] ?? '') ?: null,
            );
            $this->flash('success', 'Produit mis à jour.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect('/admin/produits/' . $id . '/modifier');
    }

    public function toggle(string $id): void
    {
        $this->requirePost();
        try {
            (new ToggleProductUseCase($this->productRepo))->execute((int) $id);
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect('/admin/produits');
    }

    public function uploadPhoto(string $id): void
    {
        $this->requirePost();
        try {
            (new UploadProductPhotoUseCase($this->productRepo, $this->uploader))
                ->execute((int) $id, $_FILES['photo']);
            $this->flash('success', 'Photo ajoutée.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect('/admin/produits/' . $id . '/modifier');
    }

    public function deletePhoto(string $photoId): void
    {
        $this->requirePost();
        try {
            $photo     = $this->productRepo->findPhotoById((int) $photoId);
            $productId = $photo?->getProductId();
            (new DeleteProductPhotoUseCase($this->productRepo))->execute((int) $photoId);
            $this->flash('success', 'Photo supprimée.');
            if ($productId) {
                $this->redirect('/admin/produits/' . $productId . '/modifier');
            }
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect('/admin/produits');
    }

    private function handlePhotoUploads(int $productId, array $filesArray): void
    {
        $useCase = new UploadProductPhotoUseCase($this->productRepo, $this->uploader);

        $count = count($filesArray['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($filesArray['error'][$i] !== UPLOAD_ERR_OK) continue;
            $file = [
                'name'     => $filesArray['name'][$i],
                'type'     => $filesArray['type'][$i],
                'tmp_name' => $filesArray['tmp_name'][$i],
                'error'    => $filesArray['error'][$i],
                'size'     => $filesArray['size'][$i],
            ];
            $useCase->execute($productId, $file);
        }
    }
}
