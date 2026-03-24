<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Settings\SettingsServiceInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Infrastructure\Config\Config;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Infrastructure\Persistence\MySqlReservationRepository;
use Rore\Presentation\Controller\Controller;
use Rore\Presentation\Http\RequestInterface;
use Rore\Presentation\Http\ResponseInterface;
use Rore\Presentation\Seo\CanonicalUrlResolver;
use Rore\Presentation\Seo\PageMetaBuilder;

class ProductController extends Controller
{
    public function __construct(
        private readonly MySqlProductRepository     $productRepo,
        private readonly MySqlCategoryRepository    $categoryRepo,
        private readonly MySqlReservationRepository $reservationRepo,
        private readonly PageMetaBuilder            $metaBuilder,
        RequestInterface                            $request,
        ResponseInterface                           $response,
        Config                                      $config,
        SessionStorageInterface                     $session,
        CsrfTokenManagerInterface                   $csrfTokenManager,
        SettingsServiceInterface                               $settings,
    ) {
        parent::__construct($request, $response, $config, $session, $csrfTokenManager, $settings);
    }

    public function show(string $path): void
    {
        $segments    = explode('/', trim($path, '/'));
        $slug        = end($segments);
        $catSegments = array_slice($segments, 0, -1);

        $product = $this->productRepo->findBySlug($slug);

        if (!$product || !$product->isActive()) {
            $this->response->setStatusCode(404);
            require BASE_PATH . '/templates/errors/404.php';
            return;
        }

        $allCategories = $this->categoryRepo->findAllActive();

        // Catégorie pour le fil d'ariane
        $urlCatSlug = !empty($catSegments) ? end($catSegments) : null;
        $category   = $urlCatSlug
            ? ($this->categoryRepo->findBySlug($urlCatSlug) ?? $this->categoryRepo->findById($product->getCategoryId()))
            : $this->categoryRepo->findById($product->getCategoryId());

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
            $reserved     = $this->reservationRepo->countReservedQtyForProduct($product->getId(), $startDate, $endDate);
            $availableQty = max(0, $product->getTotalStock() - $reserved);
        }

        $catChain     = array_slice($breadcrumb, 0, -1);
        $mainCategory = $this->categoryRepo->findById($product->getCategoryId());
        $canonicalUrl = CanonicalUrlResolver::productUrl($this->config, $product, $allCategories, $mainCategory);
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
