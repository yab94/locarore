<?php

declare(strict_types=1);

namespace Rore\Catalog\Controller\Admin;

use Rore\Catalog\UseCase\CreateProductUseCase;
use Rore\Catalog\UseCase\DeleteProductPhotoUseCase;
use Rore\Catalog\UseCase\GetAllCategoriesUseCase;
use Rore\Catalog\UseCase\GetAllProductsUseCase;
use Rore\Catalog\UseCase\GetProductEditDataUseCase;
use Rore\Catalog\UseCase\ReorderProductPhotosUseCase;
use Rore\Catalog\UseCase\ToggleProductUseCase;
use Rore\Catalog\UseCase\UpdatePhotoDescriptionUseCase;
use Rore\Catalog\UseCase\UpdateProductUseCase;
use Rore\Catalog\UseCase\UploadProductPhotoUseCase;
use Rore\Framework\Http\Route;

use Rore\Shared\Controller\AdminController;

class ProductController extends AdminController
{
    public function __construct(
        private readonly GetAllProductsUseCase        $getAllProductsUseCase,
        private readonly GetAllCategoriesUseCase      $getAllCategoriesUseCase,
        private readonly GetProductEditDataUseCase    $getProductEditDataUseCase,
        private readonly CreateProductUseCase         $createProductUseCase,
        private readonly UpdateProductUseCase         $updateProductUseCase,
        private readonly ToggleProductUseCase         $toggleProductUseCase,
        private readonly UploadProductPhotoUseCase    $uploadProductPhotoUseCase,
        private readonly DeleteProductPhotoUseCase    $deleteProductPhotoUseCase,
        private readonly UpdatePhotoDescriptionUseCase $updatePhotoDescriptionUseCase,
        private readonly ReorderProductPhotosUseCase  $reorderProductPhotosUseCase,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    #[Route('GET', '/admin/produits')]
    public function index(): void
    {
        $this->render('admin/products/list', [
            'title'    => 'Produits',
            'products' => $this->getAllProductsUseCase->execute(),
        ]);
    }

    #[Route('GET', '/admin/produits/creer')]
    public function create(): void
    {
        $this->render('admin/products/form', [
            'title'      => 'Nouveau produit',
            'product'    => null,
            'categories' => $this->getAllCategoriesUseCase->execute(),
        ]);
    }

    #[Route('POST', '/admin/produits/creer')]
    public function store(): void
    {
        $this->requirePost();
        try {
            $productId = $this->createProductUseCase->execute(
                categoryId:          $this->request->body->getInt('category_id'),
                name:                $this->request->body->getString('name'),
                description:         $this->request->body->getString('description') ?: null,
                descriptionShort:    $this->request->body->getString('description_short') ?: null,
                stock:               $this->request->body->getInt('stock'),
                priceBase:           $this->request->body->getFloat('price_base', 80.0),
                stockOnDemand:       $this->request->body->getInt('stock_on_demand'),
                fabricationTimeDays: $this->request->body->getFloat('fabrication_time_days', 0.0),
                priceExtraWeekend:   $this->request->body->getFloat('price_extra_weekend', 0.0),
                priceExtraWeekday:   $this->request->body->getFloat('price_extra_weekday', 15.0),
                extraCategoryIds:    array_map('intval', $this->request->body->getArray('extra_category_ids', [])),
                customSlug:          $this->request->body->getString('slug') ?: null,
                tagNames:            array_filter(array_map('trim', explode(',', $this->request->body->getString('tags') ?? ''))),
            );

            $this->flash('success', 'Produit créé avec succès.');
            $this->redirect($this->urlResolver->resolve(self::class . '.edit', ['id' => $productId]));
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect($this->urlResolver->resolve(self::class . '.create'));
        }
    }

    #[Route('GET', '/admin/produits/{id}/modifier')]
    public function edit(string $id): void
    {
        try {
            $data = $this->getProductEditDataUseCase->execute((int) $id);
        } catch (\Throwable) {
            $this->redirect($this->urlResolver->resolve(self::class . '.index'));
        }

        $this->render('admin/products/form', [
            'title'          => 'Modifier le produit',
            'product'        => $data['product'],
            'categories'     => $data['categories'],
            'calendarEvents' => $data['calendarEvents'],
            'productTags'    => $data['productTags'],
        ]);
    }

    #[Route('POST', '/admin/produits/{id}/modifier')]
    public function update(string $id): void
    {
        $this->requirePost();
        try {
            $this->updateProductUseCase->execute(
                id:                  (int) $id,
                categoryId:          $this->request->body->getInt('category_id'),
                name:                $this->request->body->getString('name'),
                description:         $this->request->body->getString('description') ?: null,
                descriptionShort:    $this->request->body->getString('description_short') ?: null,
                stock:               $this->request->body->getInt('stock'),
                priceBase:           $this->request->body->getFloat('price_base', 80.0),
                stockOnDemand:       $this->request->body->getInt('stock_on_demand'),
                fabricationTimeDays: $this->request->body->getFloat('fabrication_time_days', 0.0),
                priceExtraWeekend:   $this->request->body->getFloat('price_extra_weekend', 0.0),
                priceExtraWeekday:   $this->request->body->getFloat('price_extra_weekday', 15.0),
                extraCategoryIds:    array_map('intval', $this->request->body->getArray('extra_category_ids', [])),
                customSlug:          $this->request->body->getString('slug') ?: null,
                tagNames:            array_filter(array_map('trim', explode(',', $this->request->body->getString('tags') ?? ''))),
            );
            $this->flash('success', 'Produit mis à jour.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.edit', ['id' => $id]));
    }

    #[Route('POST', '/admin/produits/{id}/toggle')]
    public function toggle(string $id): void
    {
        $this->requirePost();
        try {
            $this->toggleProductUseCase->execute((int) $id);
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }

    #[Route('POST', '/admin/produits/{id}/photo')]
    public function uploadPhoto(string $id): void
    {
        $this->requirePost();
        try {
            $file = $this->request->files->getArray('photo');
            if ($file === null) {
                throw new \RuntimeException('Aucun fichier envoyé.');
            }
            $description = $this->request->body->getString('photo_description') ?? '';
            $this->uploadProductPhotoUseCase->execute((int) $id, $file, $description);
            $this->flash('success', 'Photo ajoutée.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.edit', ['id' => $id]));
    }

    #[Route('POST', '/admin/produits/photo/{photoId}/supprimer')]
    public function deletePhoto(string $photoId): void
    {
        $this->requirePost();
        try {
            $productId = $this->deleteProductPhotoUseCase->execute((int) $photoId);
            $this->flash('success', 'Photo supprimée.');
            $this->redirect($this->urlResolver->resolve(self::class . '.edit', ['id' => $productId]));
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }

    #[Route('POST', '/admin/produits/photo/{photoId}/description')]
    public function updatePhotoDescription(string $photoId): void
    {
        $this->requirePost();
        try {
            $description = $this->request->body->getString('description') ?? '';
            $productId   = $this->updatePhotoDescriptionUseCase->execute((int) $photoId, $description);
            $this->flash('success', 'Description mise à jour.');
            $this->redirect($this->urlResolver->resolve(self::class . '.edit', ['id' => $productId]));
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }

    #[Route('POST', '/admin/produits/{id}/photos/reordonner')]
    public function reorderPhotos(string $id): void
    {
        $this->requirePost();
        try {
            $orderedIds = $this->request->body->getArray('photo_ids', []);
            $this->reorderProductPhotosUseCase->execute((int) $id, array_map('intval', $orderedIds));
            $this->flash('success', 'Ordre des photos mis à jour.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.edit', ['id' => $id]));
    }
}
