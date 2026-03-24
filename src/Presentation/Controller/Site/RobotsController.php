<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Cart\CartSession;
use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Settings\SettingsServiceInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Infrastructure\Config\Config;
use Rore\Presentation\Http\RequestInterface;
use Rore\Presentation\Http\ResponseInterface;
use Rore\Presentation\Seo\UrlResolver;
use Rore\Presentation\Template\Html;

class RobotsController extends SiteController
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
    ) {
        parent::__construct($request, $response, $config, $session, $csrfTokenManager, $settings, $cart, $urlResolver, $html, $categoryRepository);
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
