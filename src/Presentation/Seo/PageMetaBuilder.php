<?php

declare(strict_types=1);

namespace Rore\Presentation\Seo;

use Rore\Application\Settings\GetSettingUseCase;
use Rore\Domain\Catalog\Entity\Category;
use Rore\Domain\Catalog\Entity\Product;
use Rore\Domain\Catalog\Entity\Tag;
use Rore\Presentation\Seo\UrlResolver;

/**
 * Construit les métadonnées SEO (PageMeta) pour chaque type de page du site.
 * S'appuie sur GetSettingUseCase (injecté) pour les settings DB.
 */
final class PageMetaBuilder
{
    public function __construct(
        private readonly GetSettingUseCase $settings,
        private readonly MetaFormatter     $meta,
        private readonly UrlResolver       $urlResolver,
    ) {}

    /**
     * Page d'accueil.
     *
     * @param Category[] $categories  toutes les catégories actives
     */
    public function forHome(array $categories): PageMeta
    {
        $siteName = $this->settings->get('site.name');
        $tagline  = $this->settings->get('site.tagline');

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

        $_og = $this->defaultOgImage();
        return new PageMeta(
            title:         $this->meta->title('Location de décoration', $siteName),
            description:   $this->meta->description(...$descParts),
            keywords:      $this->meta->keywords($kw),
            canonicalUrl:  $this->urlResolver->siteUrl() . '/',
            ogImage:       $_og['url'],
            ogImageWidth:  $_og['w'],
            ogImageHeight: $_og['h'],
        );
    }

    /**
     * Page de catégorie.
     *
     * @param Category   $category   catégorie courante
     * @param Category[] $breadcrumb chaîne racine → courante (incluse)
     */
    /**
     * @param Category[] $allCategories  toutes les catégories actives (pour URL hiérarchique)
     */
    public function forCategory(Category $category, array $breadcrumb, array $allCategories = []): PageMeta
    {
        $titleParts   = array_map(fn($c) => $c->getName(), array_reverse($breadcrumb));
        $titleParts[] = $this->settings->get('site.name');

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
            $descParts[] = $category->getName() . ' — ' . $this->settings->get('site.tagline');
        }

        $kw = ['location', $this->settings->get('site.name')];
        foreach ($breadcrumb as $crumb) {
            $kw[] = $crumb->getName();
        }

        $_og = $this->defaultOgImage();
        return new PageMeta(
            title:         $this->meta->title(...$titleParts),
            description:   $this->meta->description(...$descParts),
            keywords:      $this->meta->keywords($kw),
            canonicalUrl:  $allCategories !== [] ? $this->urlResolver->siteUrl() . $this->urlResolver->categoryUrl($category, $allCategories) : '',
            ogImage:       $_og['url'],
            ogImageWidth:  $_og['w'],
            ogImageHeight: $_og['h'],
        );
    }

    /**
     * Page produit.
     *
     * @param Product      $product      entité produit
     * @param Category|null $category     catégorie principale
     * @param Category[]    $catChain     chaîne racine → feuille (sans le produit)
     * @param string|null  $canonicalUrl URL canonique calculée par le controller
     */
    public function forProduct(
        Product  $product,
        ?Category $category,
        array     $catChain,
        string  $canonicalUrl = '',
    ): PageMeta {
        $titleParts = [$product->getName()];
        foreach (array_reverse($catChain) as $crumb) {
            $titleParts[] = $crumb->getName();
        }
        $titleParts[] = $this->settings->get('site.name');

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
                . ' — ' . $this->settings->get('site.tagline');
        }

        $kw = [$product->getName(), 'location', $this->settings->get('site.name')];
        foreach ($catChain as $crumb) {
            if (method_exists($crumb, 'getName')) {
                $kw[] = $crumb->getName();
            }
        }

        $mainPhoto = $product->getMainPhoto();

        return new PageMeta(
            title:        $this->meta->title(...$titleParts),
            description:  $this->meta->description(...$descParts),
            keywords:     $this->meta->keywords($kw),
            canonicalUrl: $canonicalUrl,
            ogImage:      $mainPhoto !== null ? $this->urlResolver->siteUrl() . $mainPhoto->getPublicPath() : '',
        );
    }

    /** @return array{url: string, w: int, h: int} */
    private function defaultOgImage(): array
    {
        return [
            'url' => $this->urlResolver->siteUrl() . '/assets/images/og-default.jpg',
            'w'   => 1200,
            'h'   => 630,
        ];
    }

    public function forCart(): PageMeta
    {
        return new PageMeta(
            title:  $this->meta->title('Mon panier', $this->settings->get('site.name')),
            robots: 'noindex, follow',
        );
    }

    public function forCheckout(): PageMeta
    {
        return new PageMeta(
            title:  $this->meta->title('Finaliser ma réservation', $this->settings->get('site.name')),
            robots: 'noindex, follow',
        );
    }

    public function forConfirmation(): PageMeta
    {
        return new PageMeta(
            title:  $this->meta->title('Demande envoyée', $this->settings->get('site.name')),
            robots: 'noindex, follow',
        );
    }

    public function forTag(Tag $tag): PageMeta
    {
        $siteName = $this->settings->get('site.name');
        $_og = $this->defaultOgImage();
        return new PageMeta(
            title:         $this->meta->title($tag->getName(), $siteName),
            description:   'Location ' . $tag->getName() . ' — ' . ($this->settings->get('site.tagline') ?: $siteName),
            keywords:      implode(', ', ['location', $tag->getName(), $siteName]),
            canonicalUrl:  $this->urlResolver->siteUrl() . $this->urlResolver->tagUrl($tag),
            ogImage:       $_og['url'],
            ogImageWidth:  $_og['w'],
            ogImageHeight: $_og['h'],
        );
    }
}
