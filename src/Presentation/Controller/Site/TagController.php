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
use Rore\Infrastructure\Persistence\MySqlTagRepository;
use Rore\Presentation\Http\RequestInterface;
use Rore\Presentation\Http\ResponseInterface;
use Rore\Presentation\Seo\PageMetaBuilder;

class TagController extends SiteController
{
    public function __construct(
        private readonly MySqlTagRepository      $tagRepo,
        private readonly MySqlProductRepository  $productRepo,
        private readonly MySqlCategoryRepository $categoryRepo,
        private readonly PageMetaBuilder         $metaBuilder,
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
        $tag = $this->tagRepo->findBySlug($slug);

        if (!$tag) {
            $this->response->setStatusCode(404);
            require BASE_PATH . '/templates/errors/404.php';
            return;
        }

        $products      = $this->productRepo->findActiveByTagSlug($slug);
        $allCategories = $this->categoryRepo->findAllActive();
        $meta          = $this->metaBuilder->forTag($tag);

        $this->render('site/tag', [
            'meta'          => $meta,
            'tag'           => $tag,
            'products'      => $products,
            'allCategories' => $allCategories,
        ]);
    }
}
