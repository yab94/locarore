<?php

declare(strict_types=1);

// ─── Constante racine ─────────────────────────────────────────────────────────
define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/lib/Bootstrap/Autoloader.php';

\RRB\Bootstrap\Autoloader::register('RRB\\', BASE_PATH . '/lib');
\RRB\Bootstrap\Autoloader::register('Rore\\', BASE_PATH . '/src');
\RRB\Bootstrap\Env::load(BASE_PATH);

$config = new \RRB\Bootstrap\Config(['BASE_PATH' => BASE_PATH]);
$config->parseIni(BASE_PATH . '/config/default.ini');
$config->parseIni(BASE_PATH . '/config/' . $config->getString('app.env') . '.ini');

\RRB\Bootstrap\PhpRuntime::apply($config->getArray('php') ?? []);

// ─── Conteneur DI ──────────────────────────────────────────────────────────
$container = new \RRB\Di\Container($config->getString('app.env') !== 'prod');
$container->register(\RRB\Bootstrap\Config::class, fn() => $config);
require BASE_PATH . '/config/bindings.php';

// ─── Router + UrlResolver ──────────────────────────────────────────────────
$scanner = new \RRB\Http\RouteScanner();
$scanner->scan(BASE_PATH . '/src/Presentation/Controller', 'Rore\Presentation\Controller');
$routes = $scanner->getRoutes();

$router = $container->get(\RRB\Http\Router::class);
$router->loadRoutes($routes);

$container->get(\RRB\Http\UrlResolver::class)->loadRoutes('Rore\Presentation\Controller', $routes);

// ─── Redirection HTTPS ────────────────────────────────────────────────────
if ($config->getString('seo.force_https', '') === $config->getString('app.env', '')) {
    $container->get(\RRB\Http\HttpsEnforcer::class)->enforce();
}

// ─── Dispatch ──────────────────────────────────────────────────────────────
$router->dispatch();
