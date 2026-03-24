<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Cart\CartSession;
use Rore\Application\Catalog\GetHomePageDataUseCase;
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

class HomeController extends SiteController
{
    public function __construct(
        private readonly GetHomePageDataUseCase  $getHomePageDataUseCase,
        private readonly PageMetaBuilder         $metaBuilder,
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
