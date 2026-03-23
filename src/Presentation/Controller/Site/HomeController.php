<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Presentation\Controller\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        $categories = (new MySqlCategoryRepository())->findAllActive();

        // Charger les produits phare (max 6 actifs)
        $allProducts = (new MySqlProductRepository())->findAllActive();
        $featured    = array_slice($allProducts, 0, 6);

        $this->render('site/home', [
            'title'         => 'Locarore — Location de décoration',
            'categories'    => $categories,
            'featured'      => $featured,
            'allCategories' => $categories,
        ]);
    }
}
