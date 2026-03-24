<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Cart\CartSession;
use Rore\Application\Catalog\GetAllActiveCategoriesUseCase;
use Rore\Application\Catalog\GetPackWithDetailsUseCase;
use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Settings\SettingsServiceInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Infrastructure\Config\Config;
use Rore\Presentation\Http\RequestInterface;
use Rore\Presentation\Http\ResponseInterface;
use Rore\Presentation\Seo\PageMeta;
use Rore\Presentation\Seo\UrlResolver;
use Rore\Presentation\Template\HtmlHelper;

class PackController extends SiteController
{
    public function __construct(
        private readonly GetPackWithDetailsUseCase      $getPackWithDetailsUseCase,
        private readonly GetAllActiveCategoriesUseCase  $getAllActiveCategoriesUseCase,
        RequestInterface                         $request,
        ResponseInterface                        $response,
        Config                                   $config,
        SessionStorageInterface                  $session,
        CsrfTokenManagerInterface                $csrfTokenManager,
        SettingsServiceInterface                 $settings,
        CartSession                              $cart,
        UrlResolver                              $urlResolver,
        HtmlHelper                                     $html,
        CategoryRepositoryInterface              $categoryRepository,
    ) {
        parent::__construct($request, $response, $config, $session, $csrfTokenManager, $settings, $cart, $urlResolver, $html, $categoryRepository);
    }

    public function show(string $slug): void
    {
        $result = $this->getPackWithDetailsUseCase->execute($slug);

        if ($result === null || !$result['pack']->isActive()) {
            $this->response->setStatusCode(404);
            require 'errors/404.php';
            return;
        }

        $pack          = $result['pack'];
        $productsById  = $result['productsById'];
        $allCategories = $result['allCategories'];

        // Produit principal (prix × quantité le plus élevé)
        $mainProductId = $pack->getMainProductId($productsById);
        $mainProduct   = $mainProductId ? ($productsById[$mainProductId] ?? null) : null;

        // Catégorie du produit principal → breadcrumb
        $mainCategory  = $mainProduct
            ? $this->findCategoryById($mainProduct->getCategoryId(), $allCategories)
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

    private function findCategoryById(int $categoryId, array $allCategories): ?\Rore\Domain\Catalog\Entity\Category
    {
        foreach ($allCategories as $cat) {
            if ($cat->getId() === $categoryId) {
                return $cat;
            }
        }
        return null;
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
