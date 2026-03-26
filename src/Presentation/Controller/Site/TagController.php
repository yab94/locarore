<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Catalog\GetAllActiveCategoriesUseCase;
use Rore\Application\Catalog\GetTagWithItemsUseCase;
use Rore\Framework\PageMeta;

use Rore\Framework\Route;
class TagController extends SiteController
{
    public function __construct(
        private readonly GetTagWithItemsUseCase         $getTagWithItemsUseCase,
        private readonly GetAllActiveCategoriesUseCase  $getAllActiveCategoriesUseCase,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    #[Route('GET', '/tags/{slug}')]
    public function show(string $slug): void
    {
        $result = $this->getTagWithItemsUseCase->execute($slug);

        if ($result === null) {
            $this->response->setStatusCode(404);
            require 'errors/404.php';
            return;
        }

        $tag          = $result['tag'];
        $products     = $result['products'];
        $packs        = $result['packs'];
        $productsById = $result['productsById'];

        $allCategories = $this->getAllActiveCategoriesUseCase->execute();

        $siteName = $this->settings->get('site.name');
        $_og = $this->defaultOgImage();
        
        $meta = new PageMeta(
            canonicalUrl: $this->slugResolver->siteUrl() . $this->slugResolver->tagUrl($tag),
            ogImage: $_og['url'],
            ogImageWidth: $_og['w'],
            ogImageHeight: $_og['h'],
            title: [$tag->getName(), $siteName],
            description: ['Location ' . $tag->getName() . ' — ' . ($this->settings->get('site.tagline') ?: $siteName)],
            keywords: ['location', $tag->getName(), $siteName],
        );

        $this->render('site/tag', [
            'meta'          => $meta,
            'tag'           => $tag,
            'breadcrumb'    => [$tag],
            'products'      => $products,
            'allCategories' => $allCategories,
            'packs'         => $packs,
            'productsById'  => $productsById,
        ]);
    }
}
