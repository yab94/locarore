<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Cart\CartSession;
use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Settings\SettingsServiceInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Domain\Catalog\Repository\PackRepositoryInterface;
use Rore\Infrastructure\Config\Config;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Presentation\Http\RequestInterface;
use Rore\Presentation\Http\ResponseInterface;
use Rore\Presentation\Seo\PageMeta;
use Rore\Presentation\Seo\UrlResolver;
use Rore\Presentation\Template\Html;

class PackController extends SiteController
{
    public function __construct(
        private readonly PackRepositoryInterface $packRepo,
        private readonly MySqlProductRepository  $productRepo,
        private readonly MySqlCategoryRepository $categoryRepo,
        RequestInterface                         $request,
        ResponseInterface                        $response,
        Config                                   $config,
        SessionStorageInterface                  $session,
        CsrfTokenManagerInterface                $csrfTokenManager,
        SettingsServiceInterface                 $settings,
        CartSession                              $cart,
        UrlResolver                              $urlResolver,
        Html                                     $html,
        CategoryRepositoryInterface              $categoryRepository,
    ) {
        parent::__construct($request, $response, $config, $session, $csrfTokenManager, $settings, $cart, $urlResolver, $html, $categoryRepository);
    }

    public function show(string $slug): void
    {
        $pack = $this->packRepo->findBySlug($slug);

        if (!$pack || !$pack->isActive()) {
            $this->response->setStatusCode(404);
            require 'errors/404.php';
            return;
        }

        // Charger tous les produits du pack indexés par ID
        $productIds = array_map(fn($i) => $i->getProductId(), $pack->getItems());
        $productsById = [];
        foreach ($productIds as $pid) {
            $p = $this->productRepo->findById($pid);
            if ($p) {
                $productsById[$p->getId()] = $p;
            }
        }

        // Produit principal (prix × quantité le plus élevé)
        $mainProductId = $pack->getMainProductId($productsById);
        $mainProduct   = $mainProductId ? ($productsById[$mainProductId] ?? null) : null;

        // Catégorie du produit principal → breadcrumb
        $allCategories = $this->categoryRepo->findAllActive();
        $mainCategory  = $mainProduct
            ? $this->categoryRepo->findById($mainProduct->getCategoryId())
            : null;

        $breadcrumb = $this->buildCategoryBreadcrumb($mainCategory, $allCategories);

        $canonicalUrl = $this->config->getStringParam('seo.packs_base_url', '/packs') . '/' . $pack->getSlug();

        $meta = new PageMeta(
            title:        $pack->getName() . ' — ' . $this->config->getStringParam('app.name'),
            description:  $pack->getDescription() ?? ($pack->getName() . ' — pack de location'),
            canonicalUrl: $canonicalUrl,
        );

        $this->render('site/pack', [
            'meta'          => $meta,
            'pack'          => $pack,
            'productsById'  => $productsById,
            'mainProduct'   => $mainProduct,
            'mainCategory'  => $mainCategory,
            'breadcrumb'    => [ ...$breadcrumb, $pack ],
            'allCategories' => $allCategories,
        ]);
    }

    /** @param \Rore\Domain\Catalog\Entity\Category[] $allCategories */
    private function buildCategoryBreadcrumb($category, array $allCategories): array
    {
        if ($category === null) {
            return [];
        }
        $byId    = [];
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
