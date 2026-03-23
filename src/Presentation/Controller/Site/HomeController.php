<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Storage\SessionStorageInterface;
use Rore\Infrastructure\Security\CsrfTokenManager;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Presentation\Controller\Controller;
use Rore\Presentation\Seo\PageMetaBuilder;

class HomeController extends Controller
{
    public function __construct(
        private readonly MySqlCategoryRepository $categoryRepo,
        private readonly MySqlProductRepository  $productRepo,
        private readonly PageMetaBuilder         $metaBuilder,
        SessionStorageInterface                  $session,
        CsrfTokenManager                         $csrfTokenManager,
    ) {
        parent::__construct($session, $csrfTokenManager);
    }

    public function index(): void
    {
        $categories = $this->categoryRepo->findAllActive();
        $featured   = array_slice($this->productRepo->findAllActive(), 0, 6);
        $meta       = $this->metaBuilder->forHome($categories);

        $this->render('site/home', [
            'meta'          => $meta,
            'categories'    => $categories,
            'featured'      => $featured,
            'allCategories' => $categories,
        ]);
    }
}
