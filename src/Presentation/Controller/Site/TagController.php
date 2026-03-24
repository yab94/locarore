<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Cart\CartSession;
use Rore\Application\Catalog\GetAllActiveCategoriesUseCase;
use Rore\Application\Catalog\GetTagWithItemsUseCase;
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

class TagController extends SiteController
{
    public function __construct(
        private readonly GetTagWithItemsUseCase         $getTagWithItemsUseCase,
        private readonly GetAllActiveCategoriesUseCase  $getAllActiveCategoriesUseCase,
        private readonly PageMetaBuilder                $metaBuilder,
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
        $meta          = $this->metaBuilder->forTag($tag);

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
