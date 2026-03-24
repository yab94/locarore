<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Cart\CartSession;
use Rore\Presentation\Seo\UrlResolver;
use Rore\Presentation\Template\Html;
use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Settings\SettingsServiceInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Application\Catalog\CreateCategoryUseCase;
use Rore\Application\Catalog\ToggleCategoryUseCase;
use Rore\Application\Catalog\UpdateCategoryUseCase;
use Rore\Infrastructure\Config\Config;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Presentation\Http\RequestInterface;
use Rore\Presentation\Http\ResponseInterface;

class CategoryController extends AdminController
{
    public function __construct(
        private readonly MySqlCategoryRepository $repo,
        private readonly CreateCategoryUseCase   $createCategoryUseCase,
        private readonly UpdateCategoryUseCase   $updateCategoryUseCase,
        private readonly ToggleCategoryUseCase   $toggleCategoryUseCase,
        RequestInterface                         $request,
        ResponseInterface                        $response,
        Config                                   $config,
        SessionStorageInterface                  $session,
        CsrfTokenManagerInterface                $csrfTokenManager,
        SettingsServiceInterface                            $settings,
        CartSession                              $cart,
        UrlResolver $urlResolver,
        Html $html,
    ) {
        parent::__construct($request, $response, $config, $session, $csrfTokenManager, $settings, $cart, $urlResolver, $html);
    }

    public function index(): void
    {
        $this->render('admin/categories/list', [
            'title'      => 'Catégories',
            'categories' => $this->repo->findAll(),
        ]);
    }

    public function create(): void
    {
        $this->render('admin/categories/form', [
            'title'      => 'Nouvelle catégorie',
            'category'   => null,
            'categories' => $this->repo->findAll(), // pour le select parent
        ]);
    }

    public function store(): void
    {
        $this->requirePost();
        try {
            $this->createCategoryUseCase->execute(
                name:             $this->request->body->getStringParam('name'),
                descriptionShort: $this->request->body->getStringParam('description_short') ?: null,
                description:      $this->request->body->getStringParam('description') ?: null,
                parentId:         $this->request->body->getStringParam('parent_id') !== '' ? $this->request->body->getIntParam('parent_id') : null,
                customSlug:       $this->request->body->getStringParam('slug') ?: null,
            );
            $this->flash('success', 'Catégorie créée avec succès.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }

    public function edit(string $id): void
    {
        $category = $this->repo->findById((int) $id);
        if (!$category) {
            $this->redirect($this->urlResolver->resolve(self::class . '.index'));
        }
        $this->render('admin/categories/form', [
            'title'      => 'Modifier la catégorie',
            'category'   => $category,
            'categories' => $this->repo->findAll(),
        ]);
    }

    public function update(string $id): void
    {
        $this->requirePost();
        try {
            $this->updateCategoryUseCase->execute(
                id:               (int) $id,
                name:             $this->request->body->getStringParam('name'),
                descriptionShort: $this->request->body->getStringParam('description_short') ?: null,
                description:      $this->request->body->getStringParam('description') ?: null,
                parentId:         $this->request->body->getStringParam('parent_id') !== '' ? $this->request->body->getIntParam('parent_id') : null,
                customSlug:       $this->request->body->getStringParam('slug') ?: null,
            );
            $this->flash('success', 'Catégorie mise à jour.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }

    public function toggle(string $id): void
    {
        $this->requirePost();
        try {
            $this->toggleCategoryUseCase->execute((int) $id);
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }
}
