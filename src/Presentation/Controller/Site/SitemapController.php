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

        $this->render('site/sitemap', [
            'baseUrl'    => $this->slugResolver->siteUrl(),
            'categories' => $data['categories'],
            'products'   => $data['products'],
            'packs'      => $data['packs'],
            'tags'       => $data['tags'],
        ], '');
    }
}
