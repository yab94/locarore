<?php

declare(strict_types=1);

namespace RRB\Http;

use RRB\Type\Castable;

/**
 * Résout les URLs canoniques des entités du catalogue.
 * Instance injectable via DI — prend Config en constructeur.
 */
class UrlResolver
{
    use Castable;

    private array $handlerToPath = [];

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

    public function __invoke(string $handler, array $params = []): string
    {
        return $this->resolve($handler, $params);
    }

    public function loadRoutes(string $controllerNamespace, array $routes): void
    {
        // POST en premier, GET écrase (GET prioritaire)
        usort($routes, fn($a, $b) => ($a['method'] === 'GET' ? 1 : 0) - ($b['method'] === 'GET' ? 1 : 0));
        foreach ($routes as $route) {
            $fqcn  = $route['handler'];
            $path  = $route['path'];
            $this->handlerToPath[$fqcn] = $path;
            $short = $this->buildRouteName($controllerNamespace, $fqcn);
            if ($short !== $fqcn) {
                $this->handlerToPath[$short] = $path;
            }
        }
    }

    protected function buildRouteName(string $controllerNamespace, string $handler): string
    {
        if ($controllerNamespace === '' || !str_starts_with($handler, $controllerNamespace . '\\')) {
            return $handler;
        }
        $short = substr($handler, strlen($controllerNamespace) + 1);
        return (string) preg_replace('/Controller(\.[a-zA-Z]+)$/', '$1', $short);
    }
}
