<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Faq\UseCase\GetAllFaqItemsUseCase;
use Rore\Application\Faq\UseCase\GetFaqItemByIdUseCase;
use Rore\Application\Faq\UseCase\CreateFaqItemUseCase;
use Rore\Application\Faq\UseCase\UpdateFaqItemUseCase;
use Rore\Application\Faq\UseCase\DeleteFaqItemUseCase;
use Rore\Application\Faq\UseCase\ToggleFaqItemUseCase;
use RRB\Http\Route;

class FaqController extends AdminController
{
    public function __construct(
        private readonly GetAllFaqItemsUseCase  $getAllFaqItemsUseCase,
        private readonly GetFaqItemByIdUseCase  $getFaqItemByIdUseCase,
        private readonly CreateFaqItemUseCase   $createFaqItemUseCase,
        private readonly UpdateFaqItemUseCase   $updateFaqItemUseCase,
        private readonly DeleteFaqItemUseCase   $deleteFaqItemUseCase,
        private readonly ToggleFaqItemUseCase   $toggleFaqItemUseCase,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    #[Route('GET', '/admin/faq')]
    public function index(): void
    {
        $this->render('admin/faq/list', [
            'title' => 'FAQ',
            'items' => $this->getAllFaqItemsUseCase->execute(),
        ]);
    }

    #[Route('GET', '/admin/faq/creer')]
    public function create(): void
    {
        $this->render('admin/faq/form', [
            'title' => 'Nouvelle question',
            'item'  => null,
        ]);
    }

    #[Route('POST', '/admin/faq/creer')]
    public function store(): void
    {
        $this->requirePost();
        try {
            $this->createFaqItemUseCase->execute(
                question:  trim($this->request->body->getString('question')),
                answer:    $this->request->body->getString('answer'),
                position:  $this->request->body->getInt('position'),
                isVisible: $this->request->body->getString('is_visible') === '1',
            );
            $this->flash('success', 'Question créée avec succès.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }

    #[Route('GET', '/admin/faq/{id}/modifier')]
    public function edit(string $id): void
    {
        $item = $this->getFaqItemByIdUseCase->execute((int) $id);
        if ($item === null) {
            $this->redirect($this->urlResolver->resolve(self::class . '.index'));
        }
        $this->render('admin/faq/form', [
            'title' => 'Modifier la question',
            'item'  => $item,
        ]);
    }

    #[Route('POST', '/admin/faq/{id}/modifier')]
    public function update(string $id): void
    {
        $this->requirePost();
        try {
            $this->updateFaqItemUseCase->execute(
                id:        (int) $id,
                question:  trim($this->request->body->getString('question')),
                answer:    $this->request->body->getString('answer'),
                position:  $this->request->body->getInt('position'),
                isVisible: $this->request->body->getString('is_visible') === '1',
            );
            $this->flash('success', 'Question mise à jour.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }

    #[Route('POST', '/admin/faq/{id}/toggle')]
    public function toggle(string $id): void
    {
        $this->requirePost();
        try {
            $this->toggleFaqItemUseCase->execute((int) $id);
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }

    #[Route('POST', '/admin/faq/{id}/supprimer')]
    public function delete(string $id): void
    {
        $this->requirePost();
        try {
            $this->deleteFaqItemUseCase->execute((int) $id);
            $this->flash('success', 'Question supprimée.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }
}
