<?php

declare(strict_types=1);

namespace Rore\Presentation\Seo;

use Rore\Domain\Catalog\Entity\Category;
use Rore\Domain\Catalog\Entity\Product;
use Rore\Domain\Catalog\Entity\Tag;
use Rore\Infrastructure\Config\Config;
use Rore\Support\Castable;

/**
 * Résout les URLs canoniques des entités du catalogue.
 * Instance injectable via DI — prend Config en constructeur.
 */
final class UrlResolver
{
    use Castable;

    private const FQCN_PREFIX = 'Rore\\Presentation\\Controller\\';

    /**
     * Double index :
     *   "FQCN.method"         → path pattern  (ex: resolve() avec ::class)
     *   "Admin\Category.edit" → path pattern  (ex: $url() alias court)
     *
     * @var array<string, string>
     */
    private array $handlerToPath = [];

    public function __construct(private readonly Config $config)
    {
        $this->buildRouteIndex();
    }

    /**
     * Résout l'URL d'un handler.
     *
     * Accepte deux formes :
     *   - FQCN complète : "Rore\...\CategoryController.edit"
     *   - Alias court   : "Admin\Category.edit"
     *
     * @param array<string, string|int> $params
     * @throws \InvalidArgumentException si le handler est introuvable
     */
    public function resolve(string $handler, array $params = []): string
    {
        $path = $this->handlerToPath[$handler] ?? null;
        if ($path === null) {
            throw new \InvalidArgumentException("No route found for handler '$handler'");
        }

        return (string) preg_replace_callback(
            '/\{([a-zA-Z_]+)\+?\}/',
            fn($m) => isset($params[$m[1]]) ? (string) $params[$m[1]] : $m[0],
            $path,
        );
    }

    /**
     * Raccourci invokable pour les templates : $url('Admin\Category.edit', [...])
     *
     * @param array<string, string|int> $params
     */
    public function __invoke(string $handler, array $params = []): string
    {
        return $this->resolve($handler, $params);
    }

    /**
     * Construit l'index handler → path depuis config.routes.
     * GET est prioritaire sur POST : si le même handler existe en GET et POST,
     * on retient le GET (pertinent pour générer des liens).
     */
    private function buildRouteIndex(): void
    {
        $routes = $this->config->getArray('routes');
        if (!is_array($routes)) {
            return;
        }
        // POST en premier, GET écrase (GET prioritaire)
        foreach (['POST', 'GET'] as $method) {
            foreach ($routes[$method] ?? [] as $path => $handler) {
                $fqcn = (string) $handler;
                $this->handlerToPath[$fqcn] = $path;
                // Indexation alias court : "Admin\Category.edit"
                $short = $this->shortAlias($fqcn);
                if ($short !== null) {
                    $this->handlerToPath[$short] = $path;
                }
            }
        }
    }

    /**
     * Convertit un handler FQCN en alias court, ou retourne null si le
     * handler n'appartient pas au préfixe contrôleur attendu.
     *
     * Exemples :
     *   "Rore\Presentation\Controller\Admin\CategoryController.edit" → "Admin\Category.edit"
     *   "Rore\Presentation\Controller\Site\CartController.index"     → "Site\Cart.index"
     */
    private function shortAlias(string $handler): ?string
    {
        if (!str_starts_with($handler, self::FQCN_PREFIX)) {
            return null;
        }
        // "Admin\CategoryController.edit"
        $short = substr($handler, strlen(self::FQCN_PREFIX));
        // Supprime "Controller" juste avant le ".method"
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
