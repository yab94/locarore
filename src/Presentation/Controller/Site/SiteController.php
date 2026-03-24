<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Cart\CartSession;
use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Settings\SettingsServiceInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Infrastructure\Config\Config;
use Rore\Presentation\Controller\Controller;
use Rore\Presentation\Http\RequestInterface;
use Rore\Presentation\Http\ResponseInterface;
use Rore\Presentation\Seo\UrlResolver;
use Rore\Presentation\Template\Html;

/**
 * Base pour tous les contrôleurs du site public.
 * Ajoute le panier et les catégories (header nav) aux données de rendu.
 */
abstract class SiteController extends Controller
{
    public function __construct(
        RequestInterface                     $request,
        ResponseInterface                    $response,
        Config                               $config,
        SessionStorageInterface              $session,
        CsrfTokenManagerInterface            $csrfTokenManager,
        SettingsServiceInterface             $settings,
        readonly CartSession                 $cart,
        UrlResolver                          $urlResolver,
        Html                                 $html,
        readonly CategoryRepositoryInterface $categoryRepository,
    ) {
        parent::__construct($request, $response, $config, $session, $csrfTokenManager, $settings, $urlResolver, $html);
    }

    protected function render(
        string $template,
        array  $data   = [],
        string $layout = 'layout/base'
    ): void {
        $data['cartItemCount']    = $this->cart->getItemCount();
        $data['headerCategories'] = $this->categoryRepository->findAllActive();
        parent::render($template, $data, $layout);
    }
}
