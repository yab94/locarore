<?php

declare(strict_types=1);

namespace Rore\Search\Controller;

use Rore\Search\UseCase\SearchUseCase;
use Rore\Framework\View\PageMeta;
use Rore\Framework\Http\Route;

use Rore\Catalog\Controller\SiteController;

class SearchController extends SiteController
{
    public function __construct(
        private readonly SearchUseCase $searchUseCase,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    #[Route('GET', '/recherche')]
    public function index(): void
    {
        $q       = trim((string) ($_GET['q'] ?? ''));
        $results = $this->searchUseCase->execute($q);

        $siteName = $this->settings->get('site.name');

        $meta = new PageMeta(
            canonicalUrl: $this->slugResolver->siteUrl() . '/recherche?q=' . urlencode($q),
            title: $q !== '' ? ['Recherche : ' . $q, $siteName] : ['Recherche', $siteName],
            description: $q !== '' ? 'Résultats de recherche pour « ' . $q . ' » sur ' . $siteName : 'Recherche de produits et packs',
            robots: 'noindex, follow',
        );

        $this->render('site/search', [
            'meta'          => $meta,
            'query'         => $results['query'],
            'products'      => $results['products'],
            'packs'         => $results['packs'],
            'productsById'  => $results['productsById'],
        ]);
    }
}