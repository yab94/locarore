<?php

declare(strict_types=1);

// ─── Constante racine ─────────────────────────────────────────────────────────
define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/src/Support/Autoloader.php';

\Rore\Support\Autoloader::register('Rore\\', BASE_PATH);
\Rore\Support\Env::load(BASE_PATH);

$config = new \Rore\Infrastructure\Config\Config(['BASE_PATH' => BASE_PATH]);
$config->parseIni(BASE_PATH . '/config/default.ini');
$config->parseIni(BASE_PATH . '/config/' . $config->getString('app.env') . '.ini');

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
