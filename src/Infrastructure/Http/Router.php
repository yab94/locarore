<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Http;

use Rore\Infrastructure\Di\Container;

class Router
{
    /** @var array{method: string, path: string, handler: array}[] */
    private array $routes = [];

    public function __construct(private readonly Container $container) {}

    public function get(string $path, array $handler): void
    {
        $this->routes[] = ['method' => 'GET', 'path' => $path, 'handler' => $handler];
    }

    public function post(string $path, array $handler): void
    {
        $this->routes[] = ['method' => 'POST', 'path' => $path, 'handler' => $handler];
    }

    public function dispatch(): void
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
        require BASE_PATH . '/templates/errors/404.php';
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
