<?php

declare(strict_types=1);

// ─── Constante racine ─────────────────────────────────────────────────────────
define('BASE_PATH', dirname(__DIR__));

// ─── Autoload (namespace Rore\ → src/) ───────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $prefix = 'Rore\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;
    $relative = substr($class, strlen($prefix));
    $file = BASE_PATH . '/src/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// ─── Config (.env + app.ini) ──────────────────────────────────────────────────
$config = \Rore\Infrastructure\Config\Config::load(BASE_PATH);

// ─── Paramètres PHP selon l'environnement (section [php] de app.ini) ─────────
(new \Rore\Infrastructure\Config\PhpRuntime($config))->boot();

// ─── Session ───────────────────────────────────────────────────────────────
session_start();

// ─── Conteneur DI ──────────────────────────────────────────────────────────
$container = new \Rore\Infrastructure\Di\Container();
$container->instance(\Rore\Infrastructure\Config\Config::class, $config);
foreach ($config->getArrayParam('di.bind') ?? [] as $abstract => $concrete) {
    $container->bind($abstract, fn($c) => $c->get($concrete));
}

// ─── Router ────────────────────────────────────────────────────────────────
$router = new \Rore\Infrastructure\Http\Router($container, $config);
$router->addRoutes($config->getArrayParam('routes') ?? []);

// ─── Dispatch ──────────────────────────────────────────────────────────────
$router->dispatch();
