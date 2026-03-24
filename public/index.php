<?php

declare(strict_types=1);

// enable error display for development (disable in production!)
ini_set('display_errors', '1');
error_reporting(E_ALL);

// ─── Constante racine ──────────────────────────────────────────────────────
define('BASE_PATH', dirname(__DIR__));

// ─── Autoload (namespace Rore\ → src/) ─────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $prefix = 'Rore\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;
    $relative = substr($class, strlen($prefix));
    $file = BASE_PATH . '/src/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});


// ─── Bootstrap (env + config + db) ─────────────────────────────────────────
$config = \Rore\Infrastructure\Config\Bootstrap::boot();

// ─── Session ───────────────────────────────────────────────────────────────
session_start();

// ─── Conteneur DI ──────────────────────────────────────────────────────────
$container = \Rore\Infrastructure\Di\ContainerFactory::create($config);

// ─── Router ────────────────────────────────────────────────────────────────
$router = new \Rore\Infrastructure\Http\Router($container);
$router->loadFromConfig($config);

// ─── Dispatch ──────────────────────────────────────────────────────────────
$router->dispatch();
