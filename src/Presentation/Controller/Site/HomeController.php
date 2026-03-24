<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Catalog\GetHomePageDataUseCase;
use Rore\Presentation\Seo\PageMetaBuilder;

class HomeController extends SiteController
{
    public function __construct(
        private readonly GetHomePageDataUseCase  $getHomePageDataUseCase,
        private readonly PageMetaBuilder         $metaBuilder,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    public function index(): void
    {
        $data       = $this->getHomePageDataUseCase->execute();
        $categories = $data['categories'];
        $products   = $data['products'];
        $tags       = $data['tags'];
        $featured   = array_slice($products, 0, 6);
        $meta       = $this->metaBuilder->forHome($categories);

        $this->render('site/home', [
            'meta'          => $meta,
            'categories'    => $categories,
            'featured'      => $featured,
            'tags'          => $tags,
            'allCategories' => $categories,
        ]);
    }
}
