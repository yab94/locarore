<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Catalog\UseCase\GetPackWithDetailsUseCase;
use RRB\View\PageMeta;

use RRB\Http\Route;
class PackController extends SiteController
{
    public function __construct(
        private readonly GetPackWithDetailsUseCase      $getPackWithDetailsUseCase,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    #[Route('GET', '/packs/{slug}')]
    public function show(string $slug): void
    {
        $result = $this->getPackWithDetailsUseCase->execute($slug);

        if ($result === null || !$result['pack']->isActive()) {
            $this->abort404();
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

        $catChain = $this->buildCategoryBreadcrumb($mainCategory, $allCategories);

        $canonicalUrl  = $this->slugResolver->siteUrl() . $this->slugResolver->packUrl($pack);
        $mainPhoto     = $mainProduct?->getMainPhoto();
        $ogImage       = $mainPhoto !== null
            ? $this->slugResolver->siteUrl() . $mainPhoto->getPublicPath()
            : $this->slugResolver->siteUrl() . '/assets/images/og-default.jpg';
        $ogImageWidth  = $mainPhoto !== null ? 0 : 1200;
        $ogImageHeight = $mainPhoto !== null ? 0 : 630;

        $titleParts = [$pack->getName()];
        foreach (array_reverse($catChain) as $crumb) {
            $titleParts[] = $crumb->getName();
        }
        $titleParts[] = $this->config->getString('app.name');

        $descParts = [];
        if ($pack->getDescription()) {
            $descParts[] = $pack->getDescription();
        }
        foreach ($catChain as $crumb) {
            if ($crumb->getDescriptionShort()) {
                $descParts[] = $crumb->getDescriptionShort();
            }
        }
        if (empty($descParts)) {
            $descParts[] = $pack->getName()
                . ($mainCategory ? ' — ' . $mainCategory->getName() : '')
                . ' — ' . $this->config->getString('app.description');
        }

        $breadcrumb = [...$catChain, $pack];

        $meta = new PageMeta(
            title:         $titleParts,
            description:   $descParts,
            canonicalUrl:  $canonicalUrl,
            ogImage:       $ogImage,
            ogImageWidth:  $ogImageWidth,
            ogImageHeight: $ogImageHeight,
            ogType:        'product',
        );

        $this->render('site/pack', [
            'meta'              => $meta,
            'pack'              => $pack,
            'productsById'      => $productsById,
            'mainProduct'       => $mainProduct,
            'mainCategory'      => $mainCategory,
            'breadcrumb'        => $breadcrumb,
            'allCategories'     => $allCategories,
            'slotsWithProducts' => $result['slotsWithProducts'],
            'packSelections'    => $this->cartState()->getPackSelections($pack->getId()),
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
