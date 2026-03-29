<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Faq\UseCase\GetVisibleFaqItemsUseCase;
use RRB\Http\Route;
use RRB\View\PageMeta;

final class FaqController extends SiteController
{
    public function __construct(
        private readonly GetVisibleFaqItemsUseCase $getVisibleFaqItems,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    #[Route('GET', '/faq')]
    public function index(): void
    {
        $this->render('site/faq', [
            'meta'  => new PageMeta(
                title: 'FAQ — ' . $this->config->getString('app.name'),
            ),
            'items' => $this->getVisibleFaqItems->execute(),
        ]);
    }
}
