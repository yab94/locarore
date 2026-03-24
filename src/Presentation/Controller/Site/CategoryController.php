<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Cart\CartSession;
use Rore\Application\Catalog\GetAllActiveCategoriesUseCase;
use Rore\Application\Catalog\GetCategoryWithItemsUseCase;
use Rore\Presentation\Seo\UrlResolver;
use Rore\Presentation\Template\HtmlHelper;
use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Settings\SettingsServiceInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Infrastructure\Config\Config;
use Rore\Presentation\Http\RequestInterface;
use Rore\Presentation\Http\ResponseInterface;
use Rore\Presentation\Seo\PageMetaBuilder;

class CategoryController extends SiteController
{
    public function __construct(
        private readonly GetCategoryWithItemsUseCase    $getCategoryWithItemsUseCase,
        private readonly GetAllActiveCategoriesUseCase  $getAllActiveCategoriesUseCase,
        private readonly PageMetaBuilder                $metaBuilder,
        RequestInterface                         $request,
        ResponseInterface                        $response,
        Config                                   $config,
        SessionStorageInterface                  $session,
        CsrfTokenManagerInterface                $csrfTokenManager,
        SettingsServiceInterface                            $settings,
        CartSession                              $cart,
        UrlResolver $urlResolver,
        HtmlHelper                                     $html,
        CategoryRepositoryInterface                  $categoryRepository,
    ) {
        parent::__construct($request, $response, $config, $session, $csrfTokenManager, $settings, $cart, $urlResolver, $html, $categoryRepository);
    }

    /**
     * $path peut être "slug" ou "parent/enfant" (multi-segments via {path+})
     */
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
        $meta          = $this->metaBuilder->forCategory($category, $breadcrumb);

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
