<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Catalog\CreateProductUseCase;
use Rore\Application\Catalog\DeleteProductPhotoUseCase;
use Rore\Application\Catalog\ToggleProductUseCase;
use Rore\Application\Catalog\UpdatePhotoDescriptionUseCase;
use Rore\Application\Catalog\UpdateProductUseCase;
use Rore\Application\Catalog\UploadProductPhotoUseCase;
use Rore\Presentation\Seo\UrlResolver;
use Rore\Presentation\Template\HtmlHelper;
use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Settings\SettingsServiceInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Infrastructure\Config\Config;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Infrastructure\Persistence\MySqlReservationRepository;
use Rore\Infrastructure\Persistence\MySqlTagRepository;
use Rore\Presentation\Http\RequestInterface;
use Rore\Presentation\Http\ResponseInterface;

class ProductController extends AdminController
{
    public function __construct(
        private readonly MySqlProductRepository     $productRepo,
        private readonly MySqlCategoryRepository    $categoryRepo,
        private readonly MySqlReservationRepository $reservationRepo,
        private readonly MySqlTagRepository         $tagRepo,
        private readonly CreateProductUseCase       $createProductUseCase,
        private readonly UpdateProductUseCase       $updateProductUseCase,
        private readonly ToggleProductUseCase       $toggleProductUseCase,
        private readonly UploadProductPhotoUseCase      $uploadProductPhotoUseCase,
        private readonly DeleteProductPhotoUseCase       $deleteProductPhotoUseCase,
        private readonly UpdatePhotoDescriptionUseCase   $updatePhotoDescriptionUseCase,
        RequestInterface                            $request,
        ResponseInterface                           $response,
        Config                                      $config,
        SessionStorageInterface                     $session,
        CsrfTokenManagerInterface                   $csrfTokenManager,
        SettingsServiceInterface                               $settings,
        UrlResolver $urlResolver,
        HtmlHelper        $html,
    ) {
        parent::__construct($request, $response, $config, $session, $csrfTokenManager, $settings, $urlResolver, $html);
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
                categoryId:       $this->request->body->getIntParam('category_id'),
                name:             $this->request->body->getStringParam('name'),
                description:      $this->request->body->getStringParam('description') ?: null,
                stock:            $this->request->body->getIntParam('stock'),
                priceBase:        $this->request->body->getFloatParam('price_base', 80.0),
                stockOnDemand:    $this->request->body->getIntParam('stock_on_demand'),
                fabricationTimeDays: $this->request->body->getFloatParam('fabrication_time_days', 0.0),
                priceExtraWeekend: $this->request->body->getFloatParam('price_extra_weekend', 0.0),
                priceExtraWeekday: $this->request->body->getFloatParam('price_extra_weekday', 15.0),
                extraCategoryIds: array_map('intval', $this->request->body->getArrayParam('extra_category_ids', [])),
                customSlug:       $this->request->body->getStringParam('slug') ?: null,
                tagNames:         array_filter(array_map('trim', explode(',', $this->request->body->getStringParam('tags') ?? ''))),
            );

            $this->flash('success', 'Produit créé avec succès.');
            $this->redirect($this->urlResolver->resolve(self::class . '.edit', ['id' => $productId]));
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect($this->urlResolver->resolve(self::class . '.create'));
        }
    }

    public function edit(string $id): void
    {
        $product = $this->productRepo->findById((int) $id);
        if (!$product) {
            $this->redirect($this->urlResolver->resolve(self::class . '.index'));
        }
        $calendarEvents = $this->reservationRepo->getReservedPeriodsByProduct($product->getId());

        $this->render('admin/products/form', [
            'title'          => 'Modifier le produit',
            'product'        => $product,
            'categories'     => $this->categoryRepo->findAll(),
            'calendarEvents' => $calendarEvents,
            'productTags'    => $this->tagRepo->findByProductId($product->getId()),
        ]);
    }

    public function update(string $id): void
    {
        $this->requirePost();
        try {
            $this->updateProductUseCase->execute(
                id:               (int) $id,
                categoryId:       $this->request->body->getIntParam('category_id'),
                name:             $this->request->body->getStringParam('name'),
                description:      $this->request->body->getStringParam('description') ?: null,
                stock:            $this->request->body->getIntParam('stock'),
                priceBase:        $this->request->body->getFloatParam('price_base', 80.0),
                stockOnDemand:    $this->request->body->getIntParam('stock_on_demand'),
                fabricationTimeDays: $this->request->body->getFloatParam('fabrication_time_days', 0.0),
                priceExtraWeekend: $this->request->body->getFloatParam('price_extra_weekend', 0.0),
                priceExtraWeekday: $this->request->body->getFloatParam('price_extra_weekday', 15.0),
                extraCategoryIds: array_map('intval', $this->request->body->getArrayParam('extra_category_ids', [])),
                customSlug:       $this->request->body->getStringParam('slug') ?: null,
                tagNames:         array_filter(array_map('trim', explode(',', $this->request->body->getStringParam('tags') ?? ''))),
            );
            $this->flash('success', 'Produit mis à jour.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.edit', ['id' => $id]));
    }

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

    public function uploadPhoto(string $id): void
    {
        $this->requirePost();
        try {
            $file = $this->request->files->getArrayParam('photo');
            if ($file === null) {
                throw new \RuntimeException('Aucun fichier envoyé.');
            }
            $description = $this->request->body->getStringParam('photo_description') ?? '';
            $this->uploadProductPhotoUseCase->execute((int) $id, $file, $description);
            $this->flash('success', 'Photo ajoutée.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.edit', ['id' => $id]));
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
                $this->redirect($this->urlResolver->resolve(self::class . '.edit', ['id' => $productId]));
            }
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }

    public function updatePhotoDescription(string $photoId): void
    {
        $this->requirePost();
        try {
            $description = $this->request->body->getStringParam('description') ?? '';
            $productId   = $this->updatePhotoDescriptionUseCase->execute((int) $photoId, $description);
            $this->flash('success', 'Description mise à jour.');
            $this->redirect($this->urlResolver->resolve(self::class . '.edit', ['id' => $productId]));
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }
}
