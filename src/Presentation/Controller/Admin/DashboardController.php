<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Catalog\GetAllCategoriesUseCase;
use Rore\Application\Catalog\GetAllProductsUseCase;
use Rore\Application\Reservation\GetReservationsUseCase;

use RRB\Http\Route;
class DashboardController extends AdminController
{
    public function __construct(
        private readonly GetAllCategoriesUseCase $getAllCategoriesUseCase,
        private readonly GetAllProductsUseCase   $getAllProductsUseCase,
        private readonly GetReservationsUseCase  $getReservationsUseCase,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    #[Route('GET', '/admin/dashboard')]
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
