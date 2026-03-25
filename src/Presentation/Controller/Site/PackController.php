<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Catalog\GetPackWithDetailsUseCase;
use Rore\Presentation\Seo\PageMeta;

class PackController extends SiteController
{
    public function __construct(
        private readonly GetPackWithDetailsUseCase      $getPackWithDetailsUseCase,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    public function show(string $slug): void
    {
        $result = $this->getPackWithDetailsUseCase->execute($slug);

        if ($result === null || !$result['pack']->isActive()) {
            $this->response->setStatusCode(404);
            require 'errors/404.php';
            return;
        }

        $pack          = $result['pack'];
        $productsById  = $result['productsById'];
        $allCategories = $result['allCategories'];

        // Produit principal (prix × quantité le plus élevé)
        $mainProductId = $pack->getMainProductId($productsById);
        $mainProduct   = $mainProductId ? ($productsById[$mainProductId] ?? null) : null;

        // Catégorie du produit principal → breadcrumb
        $mainCategory  = $mainProduct
            ? $this->findCategoryById($mainProduct->getCategoryId(), $allCategories)
            : null;

        $breadcrumb = $this->buildCategoryBreadcrumb($mainCategory, $allCategories);

        $canonicalUrl = $this->config->getStringParam('seo.packs_base_url', '/packs') . '/' . $pack->getSlug();

        $meta = new PageMeta(
            title:        $pack->getName() . ' — ' . $this->config->getStringParam('app.name'),
            description:  $pack->getDescription() ?? ($pack->getName() . ' — pack de location'),
            canonicalUrl: $canonicalUrl,
        );

        $this->render('site/pack', [
            'meta'              => $meta,
            'pack'              => $pack,
            'productsById'      => $productsById,
            'mainProduct'       => $mainProduct,
            'mainCategory'      => $mainCategory,
            'breadcrumb'        => [ ...$breadcrumb, $pack ],
            'allCategories'     => $allCategories,
            'slotsWithProducts' => $result['slotsWithProducts'],
            'packSelections'    => $this->cart->getPackSelections($pack->getId()),
        ]);
    }

    private function findCategoryById(int $categoryId, array $allCategories): ?\Rore\Domain\Catalog\Entity\Category
    {
        foreach ($allCategories as $cat) {
            if ($cat->getId() === $categoryId) {
                return $cat;
            }
        }
        return null;
    }

    /** @param \Rore\Domain\Catalog\Entity\Category[] $allCategories */
    private function buildCategoryBreadcrumb($category, array $allCategories): array
    {
        if ($category === null) {
            return [];
        }
        $byId    = [];
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
