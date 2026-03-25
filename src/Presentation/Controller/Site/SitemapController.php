<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Catalog\GetAllCatalogItemsUseCase;

class SitemapController extends SiteController
{
    public function __construct(
        private readonly GetAllCatalogItemsUseCase $getAllCatalogItemsUseCase,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    public function index(): void
    {
        $baseUrl = $this->config->getString('seo.site_url');
        
        $data       = $this->getAllCatalogItemsUseCase->execute();
        $categories = $data['categories'];
        $products   = $data['products'];
        $packs      = $data['packs'];
        $tags       = $data['tags'];

        $this->response->header('Content-Type', 'application/xml; charset=UTF-8');
        
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        // Home
        echo '<url><loc>' . htmlspecialchars($baseUrl . '/') . '</loc><lastmod>' . date('Y-m-d') . '</lastmod><priority>1.0</priority></url>';
        
        // Categories
        foreach ($categories as $cat) {
            $url = $baseUrl . $this->urlResolver->categoryUrl($cat, $categories);
            echo '<url><loc>' . htmlspecialchars($url) . '</loc><lastmod>' . $cat->getUpdatedAt()->format('Y-m-d') . '</lastmod><priority>0.8</priority></url>';
        }
        
        // Products
        foreach ($products as $product) {
            $url = $baseUrl . $this->urlResolver->productUrl($product, $categories);
            echo '<url><loc>' . htmlspecialchars($url) . '</loc><lastmod>' . $product->getUpdatedAt()->format('Y-m-d') . '</lastmod><priority>0.9</priority></url>';
        }
        
        // Packs
        foreach ($packs as $pack) {
            $url = $baseUrl . $this->config->getString('seo.packs_base_url') . '/' . $pack->getSlug();
            echo '<url><loc>' . htmlspecialchars($url) . '</loc><lastmod>' . $pack->getUpdatedAt()->format('Y-m-d') . '</lastmod><priority>0.7</priority></url>';
        }
        
        // Tags (pas de date en base — on omet lastmod)
        foreach ($tags as $tag) {
            $url = $baseUrl . $this->config->getString('seo.tags_base_url') . '/' . $tag->getSlug();
            echo '<url><loc>' . htmlspecialchars($url) . '</loc><priority>0.6</priority></url>';
        }
        
        echo '</urlset>';
        exit;
    }
}
