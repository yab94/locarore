<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use RRB\Di\BindConfig;
use RRB\Http\Route;

class RobotsController extends SiteController
{
    public function __construct(
        #[BindConfig('seo.site_url')]
        private readonly string $siteUrl,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    #[Route('GET', '/robots.txt')]
    public function index(): void
    {
        $this->response->header('Content-Type', 'text/plain; charset=UTF-8');

        echo "User-agent: *\n";
        echo "Disallow: /admin/\n";
        echo "Disallow: /panier/\n";
        echo "\n";
        echo "Sitemap: " . $this->siteUrl . "/sitemap.xml\n";
        exit;
    }
}
