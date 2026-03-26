<?php

declare(strict_types=1);

namespace Rore\Framework;

use Rore\Framework\Config;
use Rore\Framework\Castable;

/**
 * Résout les URLs canoniques des entités du catalogue.
 * Instance injectable via DI — prend Config en constructeur.
 */
class UrlResolver
{
    use Castable;

    /**
     * Double index :
     *   "FQCN.method"         → path pattern  (ex: resolve() avec ::class)
     *   "Admin\Category.edit" → path pattern  (ex: $url() alias court)
     *
     * @var array<string, string>
     */
    private array $handlerToPath = [];

    public function __construct(readonly Config $config)
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
                $short = $this->buildRouteName($fqcn);
                if ($short !== $fqcn) {
                    $this->handlerToPath[$short] = $path;
                }
            }
        }
    }
    /**
     * Construit l'alias court d'un handler FQCN.
     * Le namespace de base est lu depuis config : routes.controller_namespace
     *
     * Ex: "Rore\Presentation\Controller\Admin\CategoryController.edit" → "Admin\Category.edit"
     */
    protected function buildRouteName(string $handler): string
    {
        $ns = $this->config->getString('routes.controller_namespace', '');
        if ($ns === '' || !str_starts_with($handler, $ns . '\\')) {
            return $handler;
        }
        $short = substr($handler, strlen($ns) + 1);
        return (string) preg_replace('/Controller(\.[a-zA-Z]+)$/', '$1', $short);
    }
}
