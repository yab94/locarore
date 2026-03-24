<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Catalog\GetAllCategoriesUseCase;
use Rore\Application\Catalog\GetAllProductsUseCase;
use Rore\Presentation\Seo\UrlResolver;
use Rore\Presentation\Template\HtmlHelper;
use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Settings\SettingsServiceInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Application\Reservation\GetReservationsUseCase;
use Rore\Infrastructure\Config\Config;
use Rore\Presentation\Http\RequestInterface;
use Rore\Presentation\Http\ResponseInterface;

class DashboardController extends AdminController
{
    public function __construct(
        private readonly GetAllCategoriesUseCase $getAllCategoriesUseCase,
        private readonly GetAllProductsUseCase   $getAllProductsUseCase,
        private readonly GetReservationsUseCase  $getReservationsUseCase,
        RequestInterface                         $request,
        ResponseInterface                        $response,
        Config                                   $config,
        SessionStorageInterface                  $session,
        CsrfTokenManagerInterface                $csrfTokenManager,
        SettingsServiceInterface                            $settings,
        UrlResolver $urlResolver,
        HtmlHelper        $html,
    ) {
        parent::__construct($request, $response, $config, $session, $csrfTokenManager, $settings, $urlResolver, $html);
    }

    public function index(): void
    {
        $categories = $this->getAllCategoriesUseCase->execute();
        $products   = $this->getAllProductsUseCase->execute();
        $pending    = $this->getReservationsUseCase->pending();

        $this->render('admin/dashboard', [
            'title'           => 'Tableau de bord — Admin',
            'countCategories' => count($categories),
            'countProducts'   => count($products),
            'pendingCount'    => count($pending),
            'pendingList'     => array_slice($pending, 0, 5),
        ]);
    }
}
