<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Infrastructure\Persistence\MySqlReservationRepository;
use Rore\Presentation\Controller\Controller;

class ProductController extends Controller
{
    /**
     * $path peut être "slug" ou "categorie/slug" (multi-segments via {path+})
     */
    public function show(string $path): void
    {
        // Le dernier segment est le slug du produit
        $segments = explode('/', trim($path, '/'));
        $slug     = end($segments);

        $productRepo = new MySqlProductRepository();
        $product     = $productRepo->findBySlug($slug);

        if (!$product || !$product->isActive()) {
            http_response_code(404);
            require BASE_PATH . '/templates/errors/404.php';
            return;
        }

        // Catégorie principale (pour fil d'ariane et lien retour)
        $categoryRepo = new MySqlCategoryRepository();
        $category     = $categoryRepo->findById($product->getCategoryId());

        // Fil d'ariane de la catégorie
        $allCategories = $categoryRepo->findAllActive();
        $breadcrumb    = [];
        if ($category) {
            $breadcrumb = $this->buildCategoryBreadcrumb($category, $allCategories);
        }
        $breadcrumb[] = $product;   // Le produit lui-même en dernier

        // Stock disponible (en tenant compte des réservations en cours)
        $reservationRepo = new MySqlReservationRepository();
        $cart        = $_SESSION['rore_cart'] ?? null;
        $startDate   = $cart['start_date'] ?? null;
        $endDate     = $cart['end_date']   ?? null;

        $availableQty = $product->getTotalStock();
        if ($startDate && $endDate) {
            $reserved     = $reservationRepo->countReservedQtyForProduct($product->getId(), $startDate, $endDate);
            $availableQty = max(0, $product->getTotalStock() - $reserved);
        }

        $this->render('site/product', [
            'title'        => $product->getName() . ' — Locarore',
            'product'      => $product,
            'category'     => $category,
            'breadcrumb'   => $breadcrumb,
            'availableQty' => $availableQty,
            'cart'         => $cart,
        ]);
    }

    /**
     * @param \Rore\Domain\Catalog\Entity\Category[] $allCategories
     * @return \Rore\Domain\Catalog\Entity\Category[]
     */
    private function buildCategoryBreadcrumb($category, array $allCategories): array
    {
        $byId = [];
        foreach ($allCategories as $c) {
            $byId[$c->getId()] = $c;
        }

        $chain   = [$category];
        $current = $category;
        while ($current->getParentId() !== null && isset($byId[$current->getParentId()])) {
            $current = $byId[$current->getParentId()];
            array_unshift($chain, $current);
        }
        return $chain;
    }
}
