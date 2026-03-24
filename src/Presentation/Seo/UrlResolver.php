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
    /** @var array<string, string>  "FQCN.method" → path pattern (GET prioritaire) */
    private array $handlerToPath = [];

    public function __construct(private readonly Config $config)
    {
        $this->buildRouteIndex();
    }

    /**
     * Résout l'URL d'un handler controller sous la forme "FQCN.method".
     * Les placeholders du pattern ({id}, {path+}…) sont substitués par $params.
     *
     * Exemple :
     *   $urlResolver->resolve('Rore\...\CategoryController.edit', ['id' => 42])
     *   → "/admin/categories/42/modifier"
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
     * Construit l'index handler → path depuis config.routes.
     * GET est prioritaire sur POST : si le même handler existe en GET et POST,
     * on retient le GET (pertinent pour générer des liens).
     */
    private function buildRouteIndex(): void
    {
        $routes = $this->config->getParam('routes');
        if (!is_array($routes)) {
            return;
        }
        // POST en premier, GET écrase (GET prioritaire)
        foreach (['POST', 'GET'] as $method) {
            foreach ($routes[$method] ?? [] as $path => $handler) {
                $this->handlerToPath[(string) $handler] = $path;
            }
        }
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
