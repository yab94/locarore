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
// Scan par module + Presentation (Auth, Dashboard, Legal, Robots, Sitemap)
$_scans = [
    ['src/Presentation/Controller', 'Rore\Presentation\Controller'],
    ['src/Catalog/Controller',      'Rore\Catalog\Controller'],
    ['src/Cart/Controller',         'Rore\Cart\Controller'],
    ['src/Contact/Controller',      'Rore\Contact\Controller'],
    ['src/Search/Controller',       'Rore\Search\Controller'],
    ['src/Reservation/Controller',  'Rore\Reservation\Controller'],
    ['src/Settings/Controller',     'Rore\Settings\Controller'],
];

$_urlResolver = $container->get(\Rore\Framework\Http\UrlResolver::class);
$_allRoutes   = [];
foreach ($_scans as [$_dir, $_ns]) {
    $_scanner = new \Rore\Framework\Http\RouteScanner();
    if (is_dir(BASE_PATH . '/' . $_dir)) {
        $_scanner->scan(BASE_PATH . '/' . $_dir, $_ns);
    }
    $_routes    = $_scanner->getRoutes();
    $_allRoutes = array_merge($_allRoutes, $_routes);
    $_urlResolver->loadRoutes($_ns, $_routes);
}

$router = $container->get(\Rore\Framework\Http\Router::class);
$router->loadRoutes($_allRoutes);

// ─── Dispatch ──────────────────────────────────────────────────────────────
$router->dispatch();
