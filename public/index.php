<?php

declare(strict_types=1);

// ─── Constante racine ─────────────────────────────────────────────────────────
define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/src/Support/Autoloader.php';

\Rore\Support\Autoloader::register('Rore\\', BASE_PATH);
\Rore\Support\Env::load(BASE_PATH);

$config = new \Rore\Support\Config(['BASE_PATH' => BASE_PATH]);
$config->parseIni(BASE_PATH . '/config/default.ini');
$config->parseIni(BASE_PATH . '/config/' . $config->getString('app.env') . '.ini');

\Rore\Support\PhpRuntime::apply($config->getArray('php') ?? []);

// ─── Session ───────────────────────────────────────────────────────────────
session_start();

// ─── Conteneur DI ──────────────────────────────────────────────────────────
$container = new \Rore\Support\Container();
$container->instance(\Rore\Support\Config::class, $config);
foreach ($config->getArray('di.bind') ?? [] as $abstract => $concrete) {
    $container->bind($abstract, fn($c) => $c->get($concrete));
}

// ─── Router ────────────────────────────────────────────────────────────────
$router = $container->get(\Rore\Infrastructure\Http\Router::class);
$router->addRoutes($config->getArray('routes') ?? []);

// ─── Dispatch ──────────────────────────────────────────────────────────────
$router->dispatch();
