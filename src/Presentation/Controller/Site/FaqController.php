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
        $appName = $this->config->getString('app.name');

        $this->render('site/faq', [
            'meta'  => new PageMeta(
                title:        'FAQ — ' . $appName,
                description:  'Retrouvez les réponses aux questions fréquentes sur la location de matériel événementiel ' . $appName . '.',
                canonicalUrl: $this->slugResolver->siteUrl() . $this->urlResolver->resolve('Site\Faq.index'),
            ),
            'items' => $this->getVisibleFaqItems->execute(),
        ]);
    }
}
