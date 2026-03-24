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

class HomeController extends SiteController
{
    public function __construct(
        private readonly MySqlCategoryRepository $categoryRepo,
        private readonly MySqlProductRepository  $productRepo,
        private readonly MySqlTagRepository      $tagRepo,
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

    public function index(): void
    {
        $categories = $this->categoryRepo->findAllActive();
        $featured   = array_slice($this->productRepo->findAllActive(), 0, 6);
        $tags       = $this->tagRepo->findAll();
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
