<?php

declare(strict_types=1);

namespace Rore\Presentation\Seo;

use Rore\Domain\Catalog\Entity\Category;
use Rore\Domain\Catalog\Entity\Product;
use Rore\Domain\Catalog\Entity\Tag;
use Rore\Framework\Bootstrap\Config;
use Rore\Framework\Di\Bind;
use Rore\Framework\Type\Castable;

/**
 * Résout les URLs canoniques des entités du catalogue.
 * Instance injectable via DI — prend Config en constructeur.
 */
final class SlugResolver
{
    use Castable;

    public function __construct(
        #[Bind(static function (Config $c): string { return $c->getString('seo.site_url'); })]
        private string $siteUrl,
        #[Bind(static function (Config $c): string { return $c->getString('seo.categories_base_url'); })]
        private string $categoriesBaseUrl,
        #[Bind(static function (Config $c): string { return $c->getString('seo.products_base_url'); })]
        private string $productsBaseUrl,
        #[Bind(static function (Config $c): string { return $c->getString('seo.tags_base_url'); })]
        private string $tagsBaseUrl,
    ) {
    }
    
    /**
     * URL de base du site (ex: https://location.latyana-evenements.fr).
     */
    public function siteUrl(): string
    {
        return $this->siteUrl;
    }

    /**
     * Construit le chemin canonique d'une catégorie en remontant ses parents.
     * Ex : pour "Ballons" enfant de "Décoration" → "decoration/ballons"
     *
     * @param Category   $category
     * @param Category[] $allCategories
     * @return string  chemin sans slash initial ni final
     */
    public function categoryPath(Category $category, array $allCategories): string
    {
        $byId = [];
        foreach ($allCategories as $c) {
            $byId[$c->getId()] = $c;
        }

        $segments = [$category->getSlug()];
        $current  = $category;
        while ($current->getParentId() !== null && isset($byId[$current->getParentId()])) {
            $current = $byId[$current->getParentId()];
            array_unshift($segments, $current->getSlug());
        }
        return implode('/', $segments);
    }

    /**
     * URL complète d'une catégorie (base_url + chemin hiérarchique).
     *
     * @param Category   $category
     * @param Category[] $allCategories
     */
    public function categoryUrl(Category $category, array $allCategories): string
    {
        return $this->categoriesBaseUrl . '/' . $this->categoryPath($category, $allCategories);
    }

    /**
     * URL canonique d'un produit.
     *
     * @param Category[] $allCategories
     */
    public function productUrl(Product $product, array $allCategories, ?Category $category = null): string
    {
        if ($category === null) {
            $byId = [];
            foreach ($allCategories as $c) {
                $byId[$c->getId()] = $c;
            }
            $category = $byId[$product->getCategoryId()] ?? null;
        }

        if ($category === null) {
            return $this->productsBaseUrl . '/' . $product->getSlug();
        }

        return $this->productsBaseUrl . '/' . $this->categoryPath($category, $allCategories) . '/' . $product->getSlug();
    }

    /**
     * URL canonique d'un tag.
     */
    public function tagUrl(Tag $tag): string
    {
        return $this->tagsBaseUrl . '/' . $tag->getSlug();
    }
}
