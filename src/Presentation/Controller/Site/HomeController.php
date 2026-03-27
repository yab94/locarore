<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Catalog\UseCase\GetHomePageDataUseCase;
use RRB\View\PageMeta;
use RRB\Http\Route;

class HomeController extends SiteController
{
    public function __construct(
        private readonly GetHomePageDataUseCase  $getHomePageDataUseCase,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    #[Route('GET', '/')]
    public function index(): void
    {
        $data       = $this->getHomePageDataUseCase->execute();
        $categories = $data['categories'];
        $products   = $data['products'];
        $tags       = $data['tags'];
        $featured   = array_slice($products, 0, 6);

        $siteName  = $this->settings->get('site.name');
        $descParts = [$this->settings->get('site.tagline')];
        foreach (array_slice($categories, 0, 4) as $cat) {
            if ($cat->getDescriptionShort()) {
                $descParts[] = $cat->getDescriptionShort();
            }
        }
        $kw = [$siteName, 'location décoration', 'location matériel événement'];
        foreach ($categories as $cat) {
            $kw[] = $cat->getName();
        }
        
        $meta = new PageMeta(
            canonicalUrl: $this->slugResolver->siteUrl() . '/',
            title: ['Location de décoration', $siteName],
            description: $descParts,
            keywords: $kw,
        );

        $this->render('site/home', [
            'meta'          => $meta,
            'categories'    => $categories,
            'featured'      => $featured,
            'tags'          => $tags,
            'allCategories' => $categories,
        ]);
    }
}
