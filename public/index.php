<?php

declare(strict_types=1);

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

// ─── Helpers ───────────────────────────────────────────────────────────────
require BASE_PATH . '/lib/helpers.php';

// ─── Variables d'environnement (.env) ───────────────────────────────────
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v);
        putenv(trim($k) . '=' . trim($v));
    }
}

// ─── Config ───────────────────────────────────────────────────────────────
// Charge app.ini puis résout les placeholders ${VAR} depuis l'environnement
$iniRaw = file_get_contents(BASE_PATH . '/config/app.ini');
$iniRaw = preg_replace_callback('/\$\{([^}]+)\}/', fn($m) => getenv($m[1]) ?: $m[0], $iniRaw);
$config  = parse_ini_string($iniRaw, true);

// ─── Session ───────────────────────────────────────────────────────────────
session_start();

// ─── Base de données ───────────────────────────────────────────────────────
\Rore\Infrastructure\Database\Connection::init($config['database']);

// ─── Router ────────────────────────────────────────────────────────────────
$router = new \Rore\Infrastructure\Http\Router();

// == SITE PUBLIC ============================================================

$router->get('/',
    [\Rore\Presentation\Controller\Site\HomeController::class, 'index']);

// Catégories arborescentes : /categorie/parent  ou  /categorie/parent/enfant
$router->get('/categorie/{path+}',
    [\Rore\Presentation\Controller\Site\CategoryController::class, 'show']);

// Produits arborescents : /produit/slug  ou  /produit/cat/slug
$router->get('/produit/{path+}',
    [\Rore\Presentation\Controller\Site\ProductController::class, 'show']);

// Panier
$router->get('/panier',
    [\Rore\Presentation\Controller\Site\CartController::class, 'index']);
$router->post('/panier/dates',
    [\Rore\Presentation\Controller\Site\CartController::class, 'setDates']);
$router->post('/panier/ajouter',
    [\Rore\Presentation\Controller\Site\CartController::class, 'add']);
$router->post('/panier/supprimer',
    [\Rore\Presentation\Controller\Site\CartController::class, 'remove']);
$router->get('/panier/checkout',
    [\Rore\Presentation\Controller\Site\CartController::class, 'checkout']);
$router->post('/panier/checkout',
    [\Rore\Presentation\Controller\Site\CartController::class, 'processCheckout']);
$router->get('/panier/confirmation',
    [\Rore\Presentation\Controller\Site\CartController::class, 'confirmation']);

// == ADMIN ==================================================================

// Auth
$router->get('/admin',
    [\Rore\Presentation\Controller\Admin\AuthController::class, 'login']);
$router->post('/admin/connexion',
    [\Rore\Presentation\Controller\Admin\AuthController::class, 'processLogin']);
$router->post('/admin/deconnexion',
    [\Rore\Presentation\Controller\Admin\AuthController::class, 'logout']);

// Dashboard
$router->get('/admin/dashboard',
    [\Rore\Presentation\Controller\Admin\DashboardController::class, 'index']);

// Catégories
$router->get('/admin/categories',
    [\Rore\Presentation\Controller\Admin\CategoryController::class, 'index']);
$router->get('/admin/categories/creer',
    [\Rore\Presentation\Controller\Admin\CategoryController::class, 'create']);
$router->post('/admin/categories/creer',
    [\Rore\Presentation\Controller\Admin\CategoryController::class, 'store']);
$router->get('/admin/categories/{id}/modifier',
    [\Rore\Presentation\Controller\Admin\CategoryController::class, 'edit']);
$router->post('/admin/categories/{id}/modifier',
    [\Rore\Presentation\Controller\Admin\CategoryController::class, 'update']);
$router->post('/admin/categories/{id}/toggle',
    [\Rore\Presentation\Controller\Admin\CategoryController::class, 'toggle']);

// Produits
$router->get('/admin/produits',
    [\Rore\Presentation\Controller\Admin\ProductController::class, 'index']);
$router->get('/admin/produits/creer',
    [\Rore\Presentation\Controller\Admin\ProductController::class, 'create']);
$router->post('/admin/produits/creer',
    [\Rore\Presentation\Controller\Admin\ProductController::class, 'store']);
$router->get('/admin/produits/{id}/modifier',
    [\Rore\Presentation\Controller\Admin\ProductController::class, 'edit']);
$router->post('/admin/produits/{id}/modifier',
    [\Rore\Presentation\Controller\Admin\ProductController::class, 'update']);
$router->post('/admin/produits/{id}/toggle',
    [\Rore\Presentation\Controller\Admin\ProductController::class, 'toggle']);
$router->post('/admin/produits/{id}/photo',
    [\Rore\Presentation\Controller\Admin\ProductController::class, 'uploadPhoto']);
$router->post('/admin/produits/photo/{photoId}/supprimer',
    [\Rore\Presentation\Controller\Admin\ProductController::class, 'deletePhoto']);

// Packs
$router->get('/admin/packs',
    [\Rore\Presentation\Controller\Admin\PackController::class, 'index']);
$router->get('/admin/packs/creer',
    [\Rore\Presentation\Controller\Admin\PackController::class, 'create']);
$router->post('/admin/packs/creer',
    [\Rore\Presentation\Controller\Admin\PackController::class, 'store']);
$router->get('/admin/packs/{id}/modifier',
    [\Rore\Presentation\Controller\Admin\PackController::class, 'edit']);
$router->post('/admin/packs/{id}/modifier',
    [\Rore\Presentation\Controller\Admin\PackController::class, 'update']);
$router->post('/admin/packs/{id}/toggle',
    [\Rore\Presentation\Controller\Admin\PackController::class, 'toggle']);

// Réservations
$router->get('/admin/reservations',
    [\Rore\Presentation\Controller\Admin\ReservationController::class, 'index']);
$router->get('/admin/reservations/calendrier',
    [\Rore\Presentation\Controller\Admin\ReservationController::class, 'calendar']);
$router->get('/admin/reservations/{id}',
    [\Rore\Presentation\Controller\Admin\ReservationController::class, 'show']);
$router->post('/admin/reservations/{id}/confirmer',
    [\Rore\Presentation\Controller\Admin\ReservationController::class, 'confirm']);
$router->post('/admin/reservations/{id}/annuler',
    [\Rore\Presentation\Controller\Admin\ReservationController::class, 'cancel']);

// ─── Dispatch ──────────────────────────────────────────────────────────────
$router->dispatch();
