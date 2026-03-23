<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Presentation\Controller\Controller;
use Rore\Presentation\Seo\PageMetaBuilder;

class HomeController extends Controller
{
    public function index(): void
    {
        $categories = (new MySqlCategoryRepository())->findAllActive();

        // Charger les produits phare (max 6 actifs)
        $allProducts = (new MySqlProductRepository())->findAllActive();
        $featured    = array_slice($allProducts, 0, 6);

        $meta = (new PageMetaBuilder())->forHome($categories);

        $this->render('site/home', [
            'meta'          => $meta,
            'categories'    => $categories,
            'featured'      => $featured,
            'allCategories' => $categories,
        ]);
    }
}
