<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Reservation\GetReservationsUseCase;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Infrastructure\Persistence\MySqlReservationRepository;

class DashboardController extends AdminController
{
    public function index(): void
    {
        $categories   = (new MySqlCategoryRepository())->findAll();
        $products     = (new MySqlProductRepository())->findAll();
        $getUseCase   = new GetReservationsUseCase(new MySqlReservationRepository());
        $pending      = $getUseCase->pending();

        $this->render('admin/dashboard', [
            'title'           => 'Tableau de bord — Admin',
            'countCategories' => count($categories),
            'countProducts'   => count($products),
            'pendingCount'    => count($pending),
            'pendingList'     => array_slice($pending, 0, 5),
        ]);
    }
}
