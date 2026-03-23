<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Presentation\Controller\Controller;
use Rore\Presentation\Seo\PageMetaBuilder;

class CategoryController extends Controller
{
    public function __construct(
        private readonly MySqlCategoryRepository $categoryRepo,
        private readonly MySqlProductRepository  $productRepo,
        private readonly PageMetaBuilder         $metaBuilder,
    ) {}

    /**
     * $path peut être "slug" ou "parent/enfant" (multi-segments via {path+})
     */
    public function show(string $path): void
    {
        // Le dernier segment est le slug de la catégorie courante
        $segments = explode('/', trim($path, '/'));
        $slug     = end($segments);

        $category = $this->categoryRepo->findBySlug($slug);

        if (!$category || !$category->isActive()) {
            http_response_code(404);
            require BASE_PATH . '/templates/errors/404.php';
            return;
        }

        $products      = $this->productRepo->findActiveByCategorySlug($slug);
        $allCategories = $this->categoryRepo->findAllActive();
        $children      = array_filter($allCategories, fn($c) => $c->getParentId() === $category->getId());
        $breadcrumb    = $this->buildBreadcrumb($category, $allCategories);
        $meta          = $this->metaBuilder->forCategory($category, $breadcrumb);

        $this->render('site/category', [
            'meta'          => $meta,
            'category'      => $category,
            'products'      => $products,
            'children'      => array_values($children),
            'breadcrumb'    => $breadcrumb,
            'slugPath'      => $path,
            'allCategories' => $allCategories,
        ]);
    }

    /**
     * @param \Rore\Domain\Catalog\Entity\Category[] $allCategories
     * @return \Rore\Domain\Catalog\Entity\Category[]
     */
    private function buildBreadcrumb($category, array $allCategories): array
    {
        $byId = [];
        foreach ($allCategories as $c) {
            $byId[$c->getId()] = $c;
        }

        $chain = [$category];
        $current = $category;
        while ($current->getParentId() !== null && isset($byId[$current->getParentId()])) {
            $current = $byId[$current->getParentId()];
            array_unshift($chain, $current);
        }
        return $chain;
    }
}
