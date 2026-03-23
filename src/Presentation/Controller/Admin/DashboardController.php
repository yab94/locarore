<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Storage\SessionStorageInterface;
use Rore\Application\Reservation\GetReservationsUseCase;
use Rore\Infrastructure\Config\SettingsStore;
use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;

class DashboardController extends AdminController
{
    public function __construct(
        private readonly MySqlCategoryRepository $categoryRepo,
        private readonly MySqlProductRepository  $productRepo,
        private readonly GetReservationsUseCase  $getReservationsUseCase,
        SessionStorageInterface                  $session,
        CsrfTokenManagerInterface                $csrfTokenManager,
        SettingsStore                            $settings,
    ) {
        parent::__construct($session, $csrfTokenManager, $settings);
    }

    public function index(): void
    {
        $categories = $this->categoryRepo->findAll();
        $products   = $this->productRepo->findAll();
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
