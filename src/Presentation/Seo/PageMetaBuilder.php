<?php

declare(strict_types=1);

namespace Rore\Presentation\Seo;

use Rore\Presentation\Seo\MetaFormatter;

/**
 * Construit les métadonnées SEO (PageMeta) pour chaque type de page du site.
 * S'appuie sur MetaFormatter pour le formatage et sur setting() (lib/helpers.php).
 */
final class PageMetaBuilder
{
    /**
     * Page d'accueil.
     *
     * @param object[] $categories  toutes les catégories actives
     */
    public function forHome(array $categories): PageMeta
    {
        $siteName = setting('site.name');
        $tagline  = setting('site.tagline');

        $descParts = [$tagline];
        foreach (array_slice($categories, 0, 4) as $cat) {
            if ($cat->getDescriptionShort()) {
                $descParts[] = $cat->getDescriptionShort();
            }
        }

        $kw = [$siteName, 'location décoration', 'location matériel événement'];
        foreach ($categories as $cat) {
            $kw[] = $cat->getName();
        }

        return new PageMeta(
            title:       MetaFormatter::title('Location de décoration', $siteName),
            description: MetaFormatter::description(...$descParts),
            keywords:    MetaFormatter::keywords($kw),
        );
    }

    /**
     * Page de catégorie.
     *
     * @param object   $category   catégorie courante
     * @param object[] $breadcrumb chaîne racine → courante (incluse)
     */
    public function forCategory(object $category, array $breadcrumb): PageMeta
    {
        $titleParts   = array_map(fn($c) => $c->getName(), array_reverse($breadcrumb));
        $titleParts[] = setting('site.name');

        $descParts = [];
        foreach ($breadcrumb as $crumb) {
            if ($crumb->getDescriptionShort()) {
                $descParts[] = $crumb->getDescriptionShort();
            }
        }
        if (empty($descParts) && $category->getDescription()) {
            $descParts[] = $category->getDescription();
        }
        if (empty($descParts)) {
            $descParts[] = $category->getName() . ' — ' . setting('site.tagline');
        }

        $kw = ['location', setting('site.name')];
        foreach ($breadcrumb as $crumb) {
            $kw[] = $crumb->getName();
        }

        return new PageMeta(
            title:       MetaFormatter::title(...$titleParts),
            description: MetaFormatter::description(...$descParts),
            keywords:    MetaFormatter::keywords($kw),
        );
    }

    /**
     * Page produit.
     *
     * @param object      $product      entité produit
     * @param object|null $category     catégorie principale
     * @param object[]    $catChain     chaîne racine → feuille (sans le produit)
     * @param string|null $canonicalUrl URL canonique calculée par le controller
     */
    public function forProduct(
        object  $product,
        ?object $category,
        array   $catChain,
        ?string $canonicalUrl = null,
    ): PageMeta {
        $titleParts = [$product->getName()];
        foreach (array_reverse($catChain) as $crumb) {
            $titleParts[] = $crumb->getName();
        }
        $titleParts[] = setting('site.name');

        $descParts = [];
        if ($product->getDescription()) {
            $descParts[] = $product->getDescription();
        }
        foreach ($catChain as $crumb) {
            if (method_exists($crumb, 'getDescriptionShort') && $crumb->getDescriptionShort()) {
                $descParts[] = $crumb->getDescriptionShort();
            }
        }
        if (empty($descParts)) {
            $descParts[] = $product->getName()
                . ($category ? ' — ' . $category->getName() : '')
                . ' — ' . setting('site.tagline');
        }

        $kw = [$product->getName(), 'location', setting('site.name')];
        foreach ($catChain as $crumb) {
            if (method_exists($crumb, 'getName')) {
                $kw[] = $crumb->getName();
            }
        }

        return new PageMeta(
            title:        MetaFormatter::title(...$titleParts),
            description:  MetaFormatter::description(...$descParts),
            keywords:     MetaFormatter::keywords($kw),
            canonicalUrl: $canonicalUrl,
        );
    }

    public function forCart(): PageMeta
    {
        return new PageMeta(
            title:  MetaFormatter::title('Mon panier', setting('site.name')),
            robots: 'noindex, follow',
        );
    }

    public function forCheckout(): PageMeta
    {
        return new PageMeta(
            title:  MetaFormatter::title('Finaliser ma réservation', setting('site.name')),
            robots: 'noindex, follow',
        );
    }

    public function forConfirmation(): PageMeta
    {
        return new PageMeta(
            title:  MetaFormatter::title('Demande envoyée', setting('site.name')),
            robots: 'noindex, follow',
        );
    }
}
