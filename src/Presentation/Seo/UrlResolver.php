<?php

declare(strict_types=1);

namespace Rore\Presentation\Seo;

use Rore\Domain\Catalog\Entity\Category;
use Rore\Domain\Catalog\Entity\Product;
use Rore\Domain\Catalog\Entity\Tag;

/**
 * Résout les URLs canoniques des entités du catalogue.
 * Instance injectable via DI — prend Config en constructeur.
 */
final class UrlResolver extends \Rore\Framework\UrlResolver
{
    /**
     * Alias courts pour les handlers de type contrôleur.
     * "Rore\Presentation\Controller\Admin\CategoryController.edit" → "Admin\Category.edit"
     * "Rore\Presentation\Controller\Site\CartController.index"     → "Site\Cart.index"
     */
    protected function buildRouteName(string $handler): string
    {
        $pos = strpos($handler, '\\Controller\\');
        if ($pos === false) {
            return $handler;
        }
        $short = substr($handler, $pos + strlen('\\Controller\\'));
        return (string) preg_replace('/Controller(\.[a-zA-Z]+)$/', '$1', $short);
    }

    /**
     * URL de base du site (ex: https://location.latyana-evenements.fr).
     */
    public function siteUrl(): string
    {
        return $this->config->getString('seo.site_url');
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
        return $this->config->getString('seo.categories_base_url') . '/' . $this->categoryPath($category, $allCategories);
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
            return $this->config->getString('seo.products_base_url') . '/' . $product->getSlug();
        }

        return $this->config->getString('seo.products_base_url') . '/' . $this->categoryPath($category, $allCategories) . '/' . $product->getSlug();
    }

    /**
     * URL canonique d'un tag.
     */
    public function tagUrl(Tag $tag): string
    {
        return $this->config->getString('seo.tags_base_url') . '/' . $tag->getSlug();
    }
}
