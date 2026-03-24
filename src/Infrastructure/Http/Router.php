<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Http;

use Rore\Infrastructure\Config\Config;
use Rore\Infrastructure\Di\Container;

class Router
{
    /** @var array{method: string, path: string, handler: array}[] */
    private array $routes = [];

    public function __construct(
        private readonly Container $container,
        private readonly Config    $config,
    ) {}

    public function get(string $path, array $handler): void
    {
        $this->routes[] = ['method' => 'GET', 'path' => $path, 'handler' => $handler];
    }

    public function post(string $path, array $handler): void
    {
        $this->routes[] = ['method' => 'POST', 'path' => $path, 'handler' => $handler];
    }

    /**
     * Enregistre les routes depuis un tableau indexé par méthode HTTP.
     *
     * Format attendu :
     *   ['GET' => ['/path' => 'FQCN.method', ...], 'POST' => [...]]
     *
     * @param array<string, array<string, string>> $routes
     */
    public function addRoutes(array $routes): void
    {
        foreach (['GET', 'POST'] as $method) {
            foreach ($routes[$method] ?? [] as $path => $handler) {
                [$class, $action] = explode('.', (string) $handler, 2);
                $this->routes[] = ['method' => $method, 'path' => $path, 'handler' => [$class, $action]];
            }
        }
    }

    public function dispatch(): void
    {
        try {
            $this->doDispatch();
        } catch (\Throwable $e) {
            $this->handleError($e);
        }
    }

    private function doDispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri    = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->compile($route['path']);
            if (!preg_match($pattern, $uri, $matches)) {
                continue;
            }

            // Paramètres nommés uniquement
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            [$class, $action] = $route['handler'];
            $controller = $this->container->get($class);
            $controller->$action(...array_values($params));
            return;
        }

        http_response_code(404);
        require 'errors/404.php';
    }

    private function handleError(\Throwable $e): void
    {
        http_response_code(500);

        $isDev        = $this->config->getStringParam('app.env', 'prod') === 'dev';
        $errorMessage = $isDev
            ? $e::class . ': ' . $e->getMessage() . "\n\n" . $e->getTraceAsString()
            : null;

        require 'errors/500.php';
    }

    private function compile(string $path): string
    {
        // {name+} → capture multi-segments (avec slashes) : (?P<name>.+)
        $pattern = preg_replace('/\{([a-zA-Z_]+)\+\}/', '(?P<$1>.+)', $path);
        // {name}  → capture un segment sans slash : (?P<name>[^/]+)
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $pattern . '$#u';
    }
}
