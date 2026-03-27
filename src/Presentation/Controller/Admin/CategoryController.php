<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Catalog\UseCase\GetAllCategoriesUseCase;
use Rore\Application\Catalog\UseCase\GetCategoryByIdUseCase;
use Rore\Application\Catalog\UseCase\CreateCategoryUseCase;
use Rore\Application\Catalog\UseCase\ToggleCategoryUseCase;
use Rore\Application\Catalog\UseCase\UpdateCategoryUseCase;
use Rore\Framework\Http\Route;

class CategoryController extends AdminController
{
    public function __construct(
        private readonly GetAllCategoriesUseCase $getAllCategoriesUseCase,
        private readonly GetCategoryByIdUseCase  $getCategoryByIdUseCase,
        private readonly CreateCategoryUseCase   $createCategoryUseCase,
        private readonly UpdateCategoryUseCase   $updateCategoryUseCase,
        private readonly ToggleCategoryUseCase   $toggleCategoryUseCase,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    #[Route('GET', '/admin/categories')]
    public function index(): void
    {
        $this->render('admin/categories/list', [
            'title'      => 'Catégories',
            'categories' => $this->getAllCategoriesUseCase->execute(),
        ]);
    }

    #[Route('GET', '/admin/categories/creer')]
    public function create(): void
    {
        $this->render('admin/categories/form', [
            'title'      => 'Nouvelle catégorie',
            'category'   => null,
            'categories' => $this->getAllCategoriesUseCase->execute(), // pour le select parent
        ]);
    }

    #[Route('POST', '/admin/categories/creer')]
    public function store(): void
    {
        $this->requirePost();
        try {
            $this->createCategoryUseCase->execute(
                name:             $this->request->body->getString('name'),
                descriptionShort: $this->request->body->getString('description_short') ?: null,
                description:      $this->request->body->getString('description') ?: null,
                parentId:         $this->request->body->getString('parent_id') !== '' ? $this->request->body->getInt('parent_id') : null,
                customSlug:       $this->request->body->getString('slug') ?: null,
            );
            $this->flash('success', 'Catégorie créée avec succès.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }

    #[Route('GET', '/admin/categories/{id}/modifier')]
    public function edit(string $id): void
    {
        $category = $this->getCategoryByIdUseCase->execute((int) $id);
        if (!$category) {
            $this->redirect($this->urlResolver->resolve(self::class . '.index'));
        }
        $this->render('admin/categories/form', [
            'title'      => 'Modifier la catégorie',
            'category'   => $category,
            'categories' => $this->getAllCategoriesUseCase->execute(),
        ]);
    }

    #[Route('POST', '/admin/categories/{id}/modifier')]
    public function update(string $id): void
    {
        $this->requirePost();
        try {
            $this->updateCategoryUseCase->execute(
                id:               (int) $id,
                name:             $this->request->body->getString('name'),
                descriptionShort: $this->request->body->getString('description_short') ?: null,
                description:      $this->request->body->getString('description') ?: null,
                parentId:         $this->request->body->getString('parent_id') !== '' ? $this->request->body->getInt('parent_id') : null,
                customSlug:       $this->request->body->getString('slug') ?: null,
            );
            $this->flash('success', 'Catégorie mise à jour.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }

    #[Route('POST', '/admin/categories/{id}/toggle')]
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
