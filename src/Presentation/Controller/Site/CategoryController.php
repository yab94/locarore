<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Cart\CartSession;
use Rore\Presentation\Seo\UrlResolver;
use Rore\Presentation\Template\Html;
use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Settings\SettingsServiceInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Infrastructure\Config\Config;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Presentation\Http\RequestInterface;
use Rore\Presentation\Http\ResponseInterface;
use Rore\Presentation\Seo\PageMetaBuilder;

class CategoryController extends SiteController
{
    public function __construct(
        private readonly MySqlCategoryRepository $categoryRepo,
        private readonly MySqlProductRepository  $productRepo,
        private readonly PageMetaBuilder         $metaBuilder,
        RequestInterface                         $request,
        ResponseInterface                        $response,
        Config                                   $config,
        SessionStorageInterface                  $session,
        CsrfTokenManagerInterface                $csrfTokenManager,
        SettingsServiceInterface                            $settings,
        CartSession                              $cart,
        UrlResolver $urlResolver,
        Html                                     $html,
        CategoryRepositoryInterface                  $categoryRepository,
    ) {
        parent::__construct($request, $response, $config, $session, $csrfTokenManager, $settings, $cart, $urlResolver, $html, $categoryRepository);
    }

    /**
     * $path peut être "slug" ou "parent/enfant" (multi-segments via {path+})
     */
    public function show(string $path): void
    {
        // Le dernier segment est le slug de la catégorie courante
        $segments = explode('/', trim($path, '/'));
        $slug     = end($segments);

        $category = $this->categoryRepo->findBySlug($slug);

        if (!$category || !$category->isActive()) {
            $this->response->setStatusCode(404);
            require BASE_PATH . '/templates/errors/404.php';
            return;
        }

        $products      = $this->productRepo->findActiveByCategorySlug($slug);
        $allCategories = $this->categoryRepo->findAllActive();
        $children      = array_filter($allCategories, fn($c) => $c->getParentId() === $category->getId());
        $breadcrumb    = $this->buildBreadcrumb($category, $allCategories);
        $meta          = $this->metaBuilder->forCategory($category, $breadcrumb);

        $this->render('site/category', [
            'meta'          => $meta,
            'category'      => $category,
            'products'      => $products,
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
