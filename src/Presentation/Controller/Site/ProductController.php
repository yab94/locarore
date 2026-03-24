<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Cart\CartSession;
use Rore\Application\Catalog\GetAllActiveCategoriesUseCase;
use Rore\Application\Catalog\GetProductWithDetailsUseCase;
use Rore\Application\Reservation\GetReservedQuantityForProductUseCase;
use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Settings\SettingsServiceInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Infrastructure\Config\Config;
use Rore\Presentation\Http\RequestInterface;
use Rore\Presentation\Http\ResponseInterface;
use Rore\Presentation\Seo\UrlResolver;
use Rore\Presentation\Template\HtmlHelper;
use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Presentation\Seo\PageMetaBuilder;

class ProductController extends SiteController
{
    public function __construct(
        private readonly GetProductWithDetailsUseCase           $getProductWithDetailsUseCase,
        private readonly GetAllActiveCategoriesUseCase          $getAllActiveCategoriesUseCase,
        private readonly GetReservedQuantityForProductUseCase   $getReservedQuantityForProductUseCase,
        private readonly PageMetaBuilder                        $metaBuilder,
        RequestInterface                            $request,
        ResponseInterface                           $response,
        Config                                      $config,
        SessionStorageInterface                     $session,
        CsrfTokenManagerInterface                   $csrfTokenManager,
        SettingsServiceInterface                               $settings,
        CartSession                              $cart,
        UrlResolver $urlResolver,
        HtmlHelper                                     $html,
        CategoryRepositoryInterface                  $categoryRepository,
    ) {
        parent::__construct($request, $response, $config, $session, $csrfTokenManager, $settings, $cart, $urlResolver, $html, $categoryRepository);
    }

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

        $cart      = $this->session->get('rore_cart');
        $startDate = $cart['start_date'] ?? null;
        $endDate   = $cart['end_date']   ?? null;

        $availableQty = $product->getTotalStock();
        if ($startDate && $endDate) {
            $reserved     = $this->getReservedQuantityForProductUseCase->execute($product->getId(), $startDate, $endDate);
            $availableQty = max(0, $product->getTotalStock() - $reserved);
        }

        $catChain     = array_slice($breadcrumb, 0, -1);
        $mainCategory = $this->findCategoryById($product->getCategoryId(), $allCategories);
        $canonicalUrl = $this->urlResolver->productUrl($product, $allCategories, $mainCategory);
        $meta         = $this->metaBuilder->forProduct($product, $category, $catChain, $canonicalUrl);

        $this->render('site/product', [
            'meta'          => $meta,
            'product'       => $product,
            'category'      => $category,
            'breadcrumb'    => $breadcrumb,
            'availableQty'  => $availableQty,
            'cart'          => $cart,
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
