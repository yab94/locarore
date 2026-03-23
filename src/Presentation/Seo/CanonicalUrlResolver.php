<?php

declare(strict_types=1);

namespace Rore\Presentation\Seo;

/**
 * Résout les URLs canoniques des entités du catalogue.
 * Méthodes pures, sans état, sans dépendances externes.
 */
final class CanonicalUrlResolver
{
    /**
     * Construit le chemin canonique d'une catégorie en remontant ses parents.
     * Ex : pour "Ballons" enfant de "Décoration" → "decoration/ballons"
     *
     * @param object   $category
     * @param object[] $allCategories
     * @return string  chemin sans slash initial ni final
     */
    public static function categoryPath(object $category, array $allCategories): string
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
     * Construit l'URL canonique d'un produit incluant son chemin catégorie.
     * Ex : "/produit/decoration/ballons/arc-lumineux"
     *
     * @param object      $product
     * @param object[]    $allCategories
     * @param object|null $category  catégorie principale déjà chargée (optionnel)
     * @return string  URL absolue
     */
    public static function productUrl(object $product, array $allCategories, ?object $category = null): string
    {
        if ($category === null) {
            $byId = [];
            foreach ($allCategories as $c) {
                $byId[$c->getId()] = $c;
            }
            $category = $byId[$product->getCategoryId()] ?? null;
        }

        if ($category === null) {
            return '/produit/' . $product->getSlug();
        }

        return '/produit/' . self::categoryPath($category, $allCategories) . '/' . $product->getSlug();
    }
}
