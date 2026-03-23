<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Catalog\CreateProductUseCase;
use Rore\Application\Catalog\DeleteProductPhotoUseCase;
use Rore\Application\Catalog\ToggleProductUseCase;
use Rore\Application\Catalog\UpdateProductUseCase;
use Rore\Application\Catalog\UploadProductPhotoUseCase;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Infrastructure\Persistence\MySqlReservationRepository;

class ProductController extends AdminController
{
    public function __construct(
        private readonly MySqlProductRepository     $productRepo,
        private readonly MySqlCategoryRepository    $categoryRepo,
        private readonly MySqlReservationRepository $reservationRepo,
        private readonly CreateProductUseCase       $createProductUseCase,
        private readonly UpdateProductUseCase       $updateProductUseCase,
        private readonly ToggleProductUseCase       $toggleProductUseCase,
        private readonly UploadProductPhotoUseCase  $uploadProductPhotoUseCase,
        private readonly DeleteProductPhotoUseCase  $deleteProductPhotoUseCase,
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
            $productId = $this->createProductUseCase->execute(
                categoryId:       $this->inputInt('category_id'),
                name:             $this->inputString('name'),
                description:      $this->inputStringOrNull('description'),
                stock:            $this->inputInt('stock'),
                priceBase:        $this->inputFloat('price_base', 80.0),
                stockOnDemand:    $this->inputInt('stock_on_demand'),
                priceExtraWeekend: $this->inputFloat('price_extra_weekend', 0.0),
                priceExtraWeekday: $this->inputFloat('price_extra_weekday', 15.0),
                extraCategoryIds: array_map('intval', $this->inputArray('extra_category_ids', [])),
                customSlug:       $this->inputStringOrNull('slug'),
            );

            $photos = $this->file('photos');
            if ($photos !== null && isset($photos['name']) && is_array($photos['name'])) {
                $this->handlePhotoUploads($productId, $photos);
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
            $this->updateProductUseCase->execute(
                id:               (int) $id,
                categoryId:       $this->inputInt('category_id'),
                name:             $this->inputString('name'),
                description:      $this->inputStringOrNull('description'),
                stock:            $this->inputInt('stock'),
                priceBase:        $this->inputFloat('price_base', 80.0),
                stockOnDemand:    $this->inputInt('stock_on_demand'),
                priceExtraWeekend: $this->inputFloat('price_extra_weekend', 0.0),
                priceExtraWeekday: $this->inputFloat('price_extra_weekday', 15.0),
                extraCategoryIds: array_map('intval', $this->inputArray('extra_category_ids', [])),
                customSlug:       $this->inputStringOrNull('slug'),
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
            $this->toggleProductUseCase->execute((int) $id);
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect('/admin/produits');
    }

    public function uploadPhoto(string $id): void
    {
        $this->requirePost();
        try {
            $file = $this->file('photo');
            if ($file === null) {
                throw new \RuntimeException('Aucun fichier envoyé.');
            }
            $this->uploadProductPhotoUseCase->execute((int) $id, $file);
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
            $this->deleteProductPhotoUseCase->execute((int) $photoId);
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
        $useCase = $this->uploadProductPhotoUseCase;

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
