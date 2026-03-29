<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Catalog\UseCase\GetAllCatalogItemsUseCase;
use RRB\Http\Route;

class SitemapController extends SiteController
{
    public function __construct(
        private readonly GetAllCatalogItemsUseCase $getAllCatalogItemsUseCase,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    #[Route('GET', '/sitemap.xml')]
    public function index(): void
    {
        $data = $this->getAllCatalogItemsUseCase->execute();

        $this->response->header('Content-Type', 'application/xml; charset=UTF-8');

        $baseUrl = $this->slugResolver->siteUrl();

        $this->render('site/sitemap', [
            'baseUrl'      => $baseUrl,
            'staticUrls'   => [
                ['loc' => $baseUrl . $this->urlResolver->resolve('Site\Home.index'),    'priority' => '1.0', 'lastmod' => date('Y-m-d')],
                ['loc' => $baseUrl . $this->urlResolver->resolve('Site\Faq.index'),     'priority' => '0.5'],
                ['loc' => $baseUrl . $this->urlResolver->resolve('Site\Contact.index'), 'priority' => '0.5'],
                ['loc' => $baseUrl . $this->urlResolver->resolve('Site\Legal.mentions'),'priority' => '0.3'],
            ],
            'categories'   => $data['categories'],
            'products'     => $data['products'],
            'packs'        => $data['packs'],
            'tags'         => $data['tags'],
        ], '');
    }
}
