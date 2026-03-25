<?php

declare(strict_types=1);

// ─── Constante racine ─────────────────────────────────────────────────────────
define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/src/Support/Autoloader.php';

\Rore\Support\Autoloader::register('Rore\\', BASE_PATH);
\Rore\Support\Env::load(BASE_PATH);

// ─── Config (.env + app.ini) ──────────────────────────────────────────────────
$config = \Rore\Infrastructure\Config\Config::load(BASE_PATH);

// ─── Paramètres PHP selon l'environnement (section [php] de app.ini) ─────────
(new \Rore\Infrastructure\Config\PhpRuntime($config))->boot();

// ─── Session ───────────────────────────────────────────────────────────────
session_start();

// ─── Conteneur DI ──────────────────────────────────────────────────────────
$container = new \Rore\Infrastructure\Di\Container();
$container->instance(\Rore\Infrastructure\Config\Config::class, $config);
$container->instance(\Rore\Application\Config\ConfigInterface::class, $config);
foreach ($config->getArray('di.bind') ?? [] as $abstract => $concrete) {
    $container->bind($abstract, fn($c) => $c->get($concrete));
}

// ─── Router ────────────────────────────────────────────────────────────────
$router = $container->get(\Rore\Infrastructure\Http\Router::class);
$router->addRoutes($config->getArray('routes') ?? []);

// ─── Dispatch ──────────────────────────────────────────────────────────────
$router->dispatch();
