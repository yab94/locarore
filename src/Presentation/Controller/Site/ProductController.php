<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Catalog\GetProductWithDetailsUseCase;
use Rore\Application\Reservation\GetReservedQuantityForProductUseCase;
use Rore\Framework\PageMeta;

use Rore\Framework\Route;
class ProductController extends SiteController
{
    public function __construct(
        private readonly GetProductWithDetailsUseCase           $getProductWithDetailsUseCase,
        private readonly GetReservedQuantityForProductUseCase   $getReservedQuantityForProductUseCase,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    #[Route('GET', '/produits/{path+}')]
    public function show(string $path): void
    {
        $segments    = explode('/', trim($path, '/'));
        $slug        = end($segments);
        $catSegments = array_slice($segments, 0, -1);

        $result = $this->getProductWithDetailsUseCase->execute($slug);

        if ($result === null || !$result['product']->isActive()) {
            $this->response->setStatusCode(404);
            require 'errors/404.php';
            return;
        }

        $product       = $result['product'];
        $allCategories = $result['allCategories'];

        // Catégorie pour le fil d'ariane
        $urlCatSlug = !empty($catSegments) ? end($catSegments) : null;
        $category   = $this->findCategoryBySlugOrId($urlCatSlug, $product->getCategoryId(), $allCategories);

        // Fil d'ariane
        $breadcrumb = $category
            ? $this->buildCategoryBreadcrumb($category, $allCategories)
            : [];
        $breadcrumb[] = $product;   // Le produit lui-même en dernier

        $startDate = $this->cartState()->getStartDate();
        $endDate   = $this->cartState()->getEndDate();

        $availableQty = $product->getTotalStock();
        if ($startDate && $endDate) {
            $reserved     = $this->getReservedQuantityForProductUseCase->execute($product->getId(), $startDate, $endDate);
            $availableQty = max(0, $product->getTotalStock() - $reserved);
        }

        $catChain     = array_slice($breadcrumb, 0, -1);
        $mainCategory = $this->findCategoryById($product->getCategoryId(), $allCategories);
        $canonicalUrl = $this->slugResolver->siteUrl() . $this->slugResolver->productUrl($product, $allCategories, $mainCategory);

        $titleParts = [$product->getName()];
        foreach (array_reverse($catChain) as $crumb) {
            $titleParts[] = $crumb->getName();
        }
        $titleParts[] = $this->settings->get('site.name');
        $descParts = [];
        if ($product->getDescription()) {
            $descParts[] = $product->getDescription();
        }
        foreach ($catChain as $crumb) {
            if ($crumb->getDescriptionShort()) {
                $descParts[] = $crumb->getDescriptionShort();
            }
        }
        if (empty($descParts)) {
            $descParts[] = $product->getName()
                . ($category ? ' — ' . $category->getName() : '')
                . ' — ' . $this->settings->get('site.tagline');
        }
        $kw = [$product->getName(), 'location', $this->settings->get('site.name')];
        foreach ($catChain as $crumb) {
            $kw[] = $crumb->getName();
        }
        $mainPhoto = $product->getMainPhoto();
        
        $meta = new PageMeta(
            canonicalUrl: $canonicalUrl,
            ogImage: $mainPhoto !== null ? $this->slugResolver->siteUrl() . $mainPhoto->getPublicPath() : '',
            ogType: 'product',
            title: $titleParts,
            description: $descParts,
            keywords: $kw,
        );

        $this->render('site/product', [
            'meta'          => $meta,
            'product'       => $product,
            'category'      => $category,
            'breadcrumb'    => $breadcrumb,
            'availableQty'  => $availableQty,
            'allCategories' => $allCategories,
        ]);
    }

    private function findCategoryById(int $categoryId, array $allCategories): ?\Rore\Domain\Catalog\Entity\Category
    {
        foreach ($allCategories as $cat) {
            if ($cat->getId() === $categoryId) {
                return $cat;
            }
        }
        return null;
    }

    private function findCategoryBySlugOrId(?string $slug, int $fallbackId, array $allCategories): ?\Rore\Domain\Catalog\Entity\Category
    {
        if ($slug) {
            foreach ($allCategories as $cat) {
                if ($cat->getSlug() === $slug) {
                    return $cat;
                }
            }
        }
        return $this->findCategoryById($fallbackId, $allCategories);
    }

    /**
     * @param \Rore\Domain\Catalog\Entity\Category[] $allCategories
     * @return \Rore\Domain\Catalog\Entity\Category[]
     */
    private function buildCategoryBreadcrumb($category, array $allCategories): array
    {
        $byId = [];
        foreach ($allCategories as $c) {
            $byId[$c->getId()] = $c;
        }

        $chain   = [$category];
        $current = $category;
        while ($current->getParentId() !== null && isset($byId[$current->getParentId()])) {
            $current = $byId[$current->getParentId()];
            array_unshift($chain, $current);
        }
        return $chain;
    }
}
