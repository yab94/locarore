<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Framework\Route;
class RobotsController extends SiteController
{
    public function __construct(
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }
    #[Route('GET', '/robots.txt')]
    public function index(): void
    {
        $baseUrl = $this->config->getString('seo.site_url');
        
        $this->response->header('Content-Type', 'text/plain; charset=UTF-8');
        
        echo "User-agent: *\n";
        echo "Disallow: /admin/\n";
        echo "Disallow: /panier/\n";
        echo "\n";
        echo "Sitemap: " . $baseUrl . "/sitemap.xml\n";
        exit;
    }
}
