<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

class RobotsController extends SiteController
{
    public function __construct(
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }
    public function index(): void
    {
        $baseUrl = $this->config->getStringParam('seo.site_url');
        
        $this->response->header('Content-Type', 'text/plain; charset=UTF-8');
        
        echo "User-agent: *\n";
        echo "Disallow: /admin/\n";
        echo "Disallow: /panier/\n";
        echo "\n";
        echo "Sitemap: " . $baseUrl . "/sitemap.xml\n";
        exit;
    }
}
