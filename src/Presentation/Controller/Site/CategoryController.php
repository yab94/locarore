<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Presentation\Controller\Controller;

class CategoryController extends Controller
{
    /**
     * $path peut être "slug" ou "parent/enfant" (multi-segments via {path+})
     */
    public function show(string $path): void
    {
        // Le dernier segment est le slug de la catégorie courante
        $segments = explode('/', trim($path, '/'));
        $slug     = end($segments);

        $categoryRepo = new MySqlCategoryRepository();
        $category     = $categoryRepo->findBySlug($slug);

        if (!$category || !$category->isActive()) {
            http_response_code(404);
            require BASE_PATH . '/templates/errors/404.php';
            return;
        }

        $productRepo = new MySqlProductRepository();
        $products    = $productRepo->findActiveByCategorySlug($slug);

        // Sous-catégories actives
        $allCategories = $categoryRepo->findAllActive();
        $children      = array_filter($allCategories, fn($c) => $c->getParentId() === $category->getId());

        // Fil d'ariane : remonter les parents
        $breadcrumb = $this->buildBreadcrumb($category, $allCategories);

        $this->render('site/category', [
            'title'        => $category->getName() . ' — Locarore',
            'category'     => $category,
            'products'     => $products,
            'children'     => array_values($children),
            'breadcrumb'   => $breadcrumb,
            'slugPath'     => $path,
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
