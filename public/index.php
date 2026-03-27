<?php

declare(strict_types=1);

// ─── Constante racine ─────────────────────────────────────────────────────────
define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/src/Framework/Bootstrap/Autoloader.php';

\Rore\Framework\Bootstrap\Autoloader::register('Rore\\', BASE_PATH);
\Rore\Framework\Bootstrap\Env::load(BASE_PATH);

$config = new \Rore\Framework\Bootstrap\Config(['BASE_PATH' => BASE_PATH]);
$config->parseIni(BASE_PATH . '/config/default.ini');
$config->parseIni(BASE_PATH . '/config/' . $config->getString('app.env') . '.ini');

\Rore\Framework\Bootstrap\PhpRuntime::apply($config->getArray('php') ?? []);

// ─── Conteneur DI ──────────────────────────────────────────────────────────
$container = new \Rore\Framework\Di\Container();
$container->bind(\Rore\Framework\Bootstrap\Config::class, $config);

// ─── Router + UrlResolver ──────────────────────────────────────────────────
$scanner = new \Rore\Framework\Http\RouteScanner();
$scanner->scan(BASE_PATH . '/src/Presentation/Controller', 'Rore\Presentation\Controller');
$routes = $scanner->getRoutes();

$router = $container->get(\Rore\Framework\Http\Router::class);
$router->loadRoutes($routes);

$container->get(\Rore\Framework\Http\UrlResolver::class)->loadRoutes('Rore\Presentation\Controller', $routes);

// ─── Dispatch ──────────────────────────────────────────────────────────────
$router->dispatch();
