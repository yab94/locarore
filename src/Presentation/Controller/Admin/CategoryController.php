<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Catalog\CreateCategoryUseCase;
use Rore\Application\Catalog\ToggleCategoryUseCase;
use Rore\Application\Catalog\UpdateCategoryUseCase;
use Rore\Domain\Catalog\Service\SlugUniquenessChecker;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Infrastructure\Persistence\MySqlPackRepository;

class CategoryController extends AdminController
{
    private MySqlCategoryRepository $repo;
    private SlugUniquenessChecker   $slugChecker;

    public function __construct()
    {
        parent::__construct();
        $this->repo        = new MySqlCategoryRepository();
        $this->slugChecker = new SlugUniquenessChecker(
            $this->repo,
            new MySqlProductRepository(),
            new MySqlPackRepository(),
        );
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
            (new CreateCategoryUseCase($this->repo, $this->slugChecker))->execute(
                name:             trim($_POST['name'] ?? ''),
                descriptionShort: trim($_POST['description_short'] ?? '') ?: null,
                description:      trim($_POST['description'] ?? '') ?: null,
                parentId:         ($_POST['parent_id'] ?? '') !== '' ? (int) $_POST['parent_id'] : null,
                customSlug:       trim($_POST['slug'] ?? '') ?: null,
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
            (new UpdateCategoryUseCase($this->repo, $this->slugChecker))->execute(
                id:               (int) $id,
                name:             trim($_POST['name'] ?? ''),
                descriptionShort: trim($_POST['description_short'] ?? '') ?: null,
                description:      trim($_POST['description'] ?? '') ?: null,
                parentId:         ($_POST['parent_id'] ?? '') !== '' ? (int) $_POST['parent_id'] : null,
                customSlug:       trim($_POST['slug'] ?? '') ?: null,
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
            (new ToggleCategoryUseCase($this->repo))->execute((int) $id);
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect('/admin/categories');
    }
}
