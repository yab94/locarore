<?php

declare(strict_types=1);

namespace Rore\Presentation\Seo;

use Rore\Domain\Catalog\Entity\Category;
use Rore\Domain\Catalog\Entity\Product;
use Rore\Infrastructure\Config\Config;

/**
 * Résout les URLs canoniques des entités du catalogue.
 * Instance injectable via DI — prend Config en constructeur.
 */
final class UrlResolver
{
    public function __construct(private readonly Config $config) {}

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
        return $this->config->getStringParam('seo.categories_base_url') . '/' . $this->categoryPath($category, $allCategories);
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
            return $this->config->getStringParam('seo.products_base_url') . '/' . $product->getSlug();
        }

        return $this->config->getStringParam('seo.products_base_url') . '/' . $this->categoryPath($category, $allCategories) . '/' . $product->getSlug();
    }
}
