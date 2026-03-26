<?php

declare(strict_types=1);

namespace Rore\Framework;

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

    public function __construct(private string $controllerNamespace)
    {}

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
     * Charge les routes collectées par RouteScanner.
     * GET est prioritaire sur POST pour la génération de liens.
     *
     * @param array<array{method: string, path: string, handler: string}> $routes
     */
    public function loadRoutes(array $routes): void
    {
        // POST en premier, GET écrase (GET prioritaire)
        usort($routes, fn($a, $b) => ($a['method'] === 'GET' ? 1 : 0) - ($b['method'] === 'GET' ? 1 : 0));
        foreach ($routes as $route) {
            $fqcn  = $route['handler'];
            $path  = $route['path'];
            $this->handlerToPath[$fqcn] = $path;
            $short = $this->buildRouteName($fqcn);
            if ($short !== $fqcn) {
                $this->handlerToPath[$short] = $path;
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
        if ($this->controllerNamespace === '' || !str_starts_with($handler, $this->controllerNamespace . '\\')) {
            return $handler;
        }
        $short = substr($handler, strlen($this->controllerNamespace) + 1);
        return (string) preg_replace('/Controller(\.[a-zA-Z]+)$/', '$1', $short);
    }
}
