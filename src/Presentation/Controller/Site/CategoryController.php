<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Catalog\GetAllActiveCategoriesUseCase;
use Rore\Application\Catalog\GetCategoryWithItemsUseCase;
use Rore\Framework\PageMeta;

use Rore\Framework\Route;
class CategoryController extends SiteController
{
    public function __construct(
        private readonly GetCategoryWithItemsUseCase    $getCategoryWithItemsUseCase,
        private readonly GetAllActiveCategoriesUseCase  $getAllActiveCategoriesUseCase,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    /**
     * $path peut être "slug" ou "parent/enfant" (multi-segments via {path+})
     */
    #[Route('GET', '/categories/{path+}')]
    public function show(string $path): void
    {
        $result = $this->getCategoryWithItemsUseCase->execute($path);

        if ($result === null || !$result['category']->isActive()) {
            $this->response->setStatusCode(404);
            require 'errors/404.php';
            return;
        }

        $category     = $result['category'];
        $products     = $result['products'];
        $packs        = $result['packs'];
        $productsById = $result['productsById'];

        $allCategories = $this->getAllActiveCategoriesUseCase->execute();
        $children      = array_filter($allCategories, fn($c) => $c->getParentId() === $category->getId());
        $breadcrumb    = $this->buildBreadcrumb($category, $allCategories);

        $titleParts   = array_map(fn($c) => $c->getName(), array_reverse($breadcrumb));
        $titleParts[] = $this->settings->get('site.name');
        $descParts = [];
        foreach ($breadcrumb as $crumb) {
            if ($crumb->getDescriptionShort()) $descParts[] = $crumb->getDescriptionShort();
        }
        if (empty($descParts) && $category->getDescription()) {
            $descParts[] = $category->getDescription();
        }
        if (empty($descParts)) {
            $descParts[] = $category->getName() . ' — ' . $this->settings->get('site.tagline');
        }
        $kw = ['location', $this->settings->get('site.name')];
        foreach ($breadcrumb as $crumb) {
            $kw[] = $crumb->getName();
        }
        $_og = $this->defaultOgImage();
        
        $meta = new PageMeta(
            canonicalUrl: $allCategories !== [] ? $this->slugResolver->siteUrl() . $this->slugResolver->categoryUrl($category, $allCategories) : '',
            ogImage: $_og['url'],
            ogImageWidth: $_og['w'],
            ogImageHeight: $_og['h'],
            title: $titleParts,
            description: $descParts,
            keywords: $kw,
        );

        $this->render('site/category', [
            'meta'          => $meta,
            'category'      => $category,
            'products'      => $products,
            'packs'         => $packs,
            'productsById'  => $productsById,
            'children'      => array_values($children),
            'breadcrumb'    => $breadcrumb,
            'slugPath'      => $path,
            'allCategories' => $allCategories,
        ]);
    }

    /**
     * @param \Rore\Domain\Catalog\Entity\Category[] $allCategories
     * @return \Rore\Domain\Catalog\Entity\Category[]
     */
    private function buildBreadcrumb($category, array $allCategories): array
    {
        $byId = [];
        foreach ($allCategories as $c) {
            $byId[$c->getId()] = $c;
        }

        $chain = [$category];
        $current = $category;
        while ($current->getParentId() !== null && isset($byId[$current->getParentId()])) {
            $current = $byId[$current->getParentId()];
            array_unshift($chain, $current);
        }
        return $chain;
    }
}
