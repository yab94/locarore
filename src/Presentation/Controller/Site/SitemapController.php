<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Cart\CartSession;
use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Settings\SettingsServiceInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Domain\Catalog\Repository\PackRepositoryInterface;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Domain\Catalog\Repository\TagRepositoryInterface;
use Rore\Infrastructure\Config\Config;
use Rore\Presentation\Http\RequestInterface;
use Rore\Presentation\Http\ResponseInterface;
use Rore\Presentation\Seo\UrlResolver;
use Rore\Presentation\Template\Html;

class SitemapController extends SiteController
{
    public function __construct(
        RequestInterface                     $request,
        ResponseInterface                    $response,
        Config                               $config,
        SessionStorageInterface              $session,
        CsrfTokenManagerInterface            $csrfTokenManager,
        SettingsServiceInterface             $settings,
        CartSession                          $cart,
        UrlResolver                          $urlResolver,
        Html                                 $html,
        CategoryRepositoryInterface          $categoryRepository,
        private readonly ProductRepositoryInterface  $productRepo,
        private readonly PackRepositoryInterface     $packRepo,
        private readonly TagRepositoryInterface      $tagRepo,
    ) {
        parent::__construct($request, $response, $config, $session, $csrfTokenManager, $settings, $cart, $urlResolver, $html, $categoryRepository);
    }

    public function index(): void
    {
        $baseUrl = $this->config->getStringParam('seo.site_url');
        
        $categories = $this->categoryRepository->findAllActive();
        $products   = $this->productRepo->findAllActive();
        $packs      = $this->packRepo->findAllActive();
        $tags       = $this->tagRepo->findAll();

        $this->response->header('Content-Type', 'application/xml; charset=UTF-8');
        
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        // Home
        echo '<url><loc>' . htmlspecialchars($baseUrl . '/') . '</loc><priority>1.0</priority></url>';
        
        // Categories
        foreach ($categories as $cat) {
            $url = $baseUrl . $this->urlResolver->categoryUrl($cat, $categories);
            echo '<url><loc>' . htmlspecialchars($url) . '</loc><priority>0.8</priority></url>';
        }
        
        // Products
        foreach ($products as $product) {
            $url = $baseUrl . $this->urlResolver->productUrl($product, $categories);
            echo '<url><loc>' . htmlspecialchars($url) . '</loc><priority>0.9</priority></url>';
        }
        
        // Packs
        foreach ($packs as $pack) {
            $url = $baseUrl . $this->config->getStringParam('seo.packs_base_url') . '/' . $pack->getSlug();
            echo '<url><loc>' . htmlspecialchars($url) . '</loc><priority>0.7</priority></url>';
        }
        
        // Tags
        foreach ($tags as $tag) {
            $url = $baseUrl . $this->config->getStringParam('seo.tags_base_url') . '/' . $tag->getSlug();
            echo '<url><loc>' . htmlspecialchars($url) . '</loc><priority>0.6</priority></url>';
        }
        
        echo '</urlset>';
        exit;
    }
}
