<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Catalog\CreateCategoryUseCase;
use Rore\Application\Catalog\ToggleCategoryUseCase;
use Rore\Application\Catalog\UpdateCategoryUseCase;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;

class CategoryController extends AdminController
{
    public function __construct(
        private readonly MySqlCategoryRepository $repo,
        private readonly CreateCategoryUseCase   $createCategoryUseCase,
        private readonly UpdateCategoryUseCase   $updateCategoryUseCase,
        private readonly ToggleCategoryUseCase   $toggleCategoryUseCase,
    ) {
        parent::__construct();
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
                name:             $this->inputString('name'),
                descriptionShort: $this->inputStringOrNull('description_short'),
                description:      $this->inputStringOrNull('description'),
                parentId:         $this->inputString('parent_id') !== '' ? $this->inputInt('parent_id') : null,
                customSlug:       $this->inputStringOrNull('slug'),
            );
            $this->flash('success', 'Catégorie créée avec succès.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect('/admin/categories');
    }

    public function edit(string $id): void
    {
        $category = $this->repo->findById((int) $id);
        if (!$category) {
            $this->redirect('/admin/categories');
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
                name:             $this->inputString('name'),
                descriptionShort: $this->inputStringOrNull('description_short'),
                description:      $this->inputStringOrNull('description'),
                parentId:         $this->inputString('parent_id') !== '' ? $this->inputInt('parent_id') : null,
                customSlug:       $this->inputStringOrNull('slug'),
            );
            $this->flash('success', 'Catégorie mise à jour.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect('/admin/categories');
    }

    public function toggle(string $id): void
    {
        $this->requirePost();
        try {
            $this->toggleCategoryUseCase->execute((int) $id);
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect('/admin/categories');
    }
}
