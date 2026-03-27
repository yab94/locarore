#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Dispatch la couche Presentation dans les modules.
 *
 * Déplacements :
 *   Shared/Controller/Controller.php         ← Presentation/Controller/Controller.php
 *   Shared/Controller/AdminController.php    ← Presentation/Controller/Admin/AdminController.php
 *   Shared/Security/LoginRateLimiter.php     ← Presentation/Security/LoginRateLimiter.php
 *   Catalog/Seo/SlugResolver.php             ← Presentation/Seo/SlugResolver.php
 *   Catalog/Controller/SiteController.php    ← Presentation/Controller/Site/SiteController.php
 *   Catalog/Controller/Site/...              ← Presentation/Controller/Site/{Category,Product,Pack,Tag,Home}Controller.php
 *   Catalog/Controller/Admin/...             ← Presentation/Controller/Admin/{Category,Product,Pack}Controller.php
 *   Cart/Controller/CartController.php       ← Presentation/Controller/Site/CartController.php
 *   Contact/Controller/Site/...              ← Presentation/Controller/Site/ContactController.php
 *   Contact/Controller/Admin/...             ← Presentation/Controller/Admin/MessageController.php
 *   Search/Controller/SearchController.php   ← Presentation/Controller/Site/SearchController.php
 *   Reservation/Controller/Admin/...         ← Presentation/Controller/Admin/ReservationController.php
 *   Settings/Controller/Admin/...            ← Presentation/Controller/Admin/SettingsController.php
 *
 * Restent dans Presentation/ :
 *   Controller/Admin/{Auth,Dashboard}Controller.php
 *   Controller/Site/{Legal,Robots,Sitemap}Controller.php
 */

$base = dirname(__DIR__);

// ─── Helpers ─────────────────────────────────────────────────────────────────

function ensureDir(string $path): void
{
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        echo "mkdir  $path\n";
    }
}

/**
 * Déplace un fichier en mettant à jour son namespace et des remplacements supplémentaires.
 */
function moveFile(string $from, string $to, string $oldNs, string $newNs, array $extras = []): void
{
    if (!file_exists($from)) {
        echo "SKIP   $from (introuvable)\n";
        return;
    }
    ensureDir(dirname($to));
    $content = file_get_contents($from);
    $content = str_replace("namespace $oldNs;", "namespace $newNs;", $content);
    foreach ($extras as [$s, $r]) {
        $content = str_replace($s, $r, $content);
    }
    file_put_contents($to, $content);
    unlink($from);
    echo "move   $from\n    →  $to\n";
}

/**
 * Met à jour des remplacements dans un fichier existant.
 */
function patchFile(string $path, array $replacements): void
{
    if (!file_exists($path)) {
        echo "SKIP   $path (introuvable)\n";
        return;
    }
    $content = file_get_contents($path);
    $before  = $content;
    foreach ($replacements as [$s, $r]) {
        $content = str_replace($s, $r, $content);
    }
    if ($content !== $before) {
        file_put_contents($path, $content);
        echo "patch  $path\n";
    }
}

/**
 * Remplace dans tous les fichiers PHP d'un répertoire et optionnellement templates/.
 */
function replaceGlobally(string $dir, string $search, string $replace): int
{
    $n  = 0;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $file) {
        if (!$file->isFile()) continue;
        if ($file->getExtension() !== 'php') continue;
        $content = file_get_contents($file->getPathname());
        if (str_contains($content, $search)) {
            file_put_contents($file->getPathname(), str_replace($search, $replace, $content));
            $n++;
        }
    }
    return $n;
}

// ─── Étape 1 : Créer les répertoires cibles ───────────────────────────────────
$dirs = [
    "$base/src/Shared/Controller",
    "$base/src/Shared/Security",
    "$base/src/Catalog/Seo",
    "$base/src/Catalog/Controller",
    "$base/src/Catalog/Controller/Site",
    "$base/src/Catalog/Controller/Admin",
    "$base/src/Cart/Controller",
    "$base/src/Contact/Controller",
    "$base/src/Contact/Controller/Site",
    "$base/src/Contact/Controller/Admin",
    "$base/src/Search/Controller",
    "$base/src/Reservation/Controller",
    "$base/src/Reservation/Controller/Admin",
    "$base/src/Settings/Controller",
    "$base/src/Settings/Controller/Admin",
];
foreach ($dirs as $d) ensureDir($d);

echo "\n=== Déplacements ===\n";

// ─── Étape 2 : Controller base → Shared ──────────────────────────────────────
moveFile(
    "$base/src/Presentation/Controller/Controller.php",
    "$base/src/Shared/Controller/Controller.php",
    'Rore\Presentation\Controller',
    'Rore\Shared\Controller',
    [
        ['use Rore\Presentation\Seo\SlugResolver;', 'use Rore\Catalog\Seo\SlugResolver;'],
    ]
);

// ─── AdminController → Shared (supprime la dépendance sur AuthController) ────
moveFile(
    "$base/src/Presentation/Controller/Admin/AdminController.php",
    "$base/src/Shared/Controller/AdminController.php",
    'Rore\Presentation\Controller\Admin',
    'Rore\Shared\Controller',
    [
        // Supprime l'import AuthController
        ["use Rore\Presentation\Controller\Admin\AuthController;\n", ''],
        // Supprime l'import Controller (même namespace maintenant)
        ["use Rore\Presentation\Controller\Controller;\n", ''],
        // Remplace la résolution dynamique par le chemin en dur
        ["resolve(AuthController::class . '.login')", "resolve('/admin')"],
    ]
);

// ─── LoginRateLimiter → Shared/Security ──────────────────────────────────────
moveFile(
    "$base/src/Presentation/Security/LoginRateLimiter.php",
    "$base/src/Shared/Security/LoginRateLimiter.php",
    'Rore\Presentation\Security',
    'Rore\Shared\Security'
);

// ─── SlugResolver → Catalog/Seo ──────────────────────────────────────────────
moveFile(
    "$base/src/Presentation/Seo/SlugResolver.php",
    "$base/src/Catalog/Seo/SlugResolver.php",
    'Rore\Presentation\Seo',
    'Rore\Catalog\Seo'
);

// ─── SiteController → Catalog/Controller ────────────────────────────────────
moveFile(
    "$base/src/Presentation/Controller/Site/SiteController.php",
    "$base/src/Catalog/Controller/SiteController.php",
    'Rore\Presentation\Controller\Site',
    'Rore\Catalog\Controller',
    [
        ["use Rore\Presentation\Controller\Controller;\n", "use Rore\Shared\Controller\Controller;\n"],
    ]
);

// ─── Catalog site controllers → Catalog/Controller/Site ──────────────────────
$catalogSite = ['Category', 'Product', 'Pack', 'Tag', 'Home'];
foreach ($catalogSite as $name) {
    moveFile(
        "$base/src/Presentation/Controller/Site/{$name}Controller.php",
        "$base/src/Catalog/Controller/Site/{$name}Controller.php",
        'Rore\Presentation\Controller\Site',
        'Rore\Catalog\Controller\Site',
        [
            // SiteController était dans le même namespace, ajouter l'import explicite
            ["class {$name}Controller extends SiteController", "use Rore\Catalog\Controller\SiteController;\n\nclass {$name}Controller extends SiteController"],
        ]
    );
}

// ─── Catalog admin controllers → Catalog/Controller/Admin ───────────────────
$catalogAdmin = ['Category', 'Product', 'Pack'];
foreach ($catalogAdmin as $name) {
    moveFile(
        "$base/src/Presentation/Controller/Admin/{$name}Controller.php",
        "$base/src/Catalog/Controller/Admin/{$name}Controller.php",
        'Rore\Presentation\Controller\Admin',
        'Rore\Catalog\Controller\Admin',
        [
            ["class {$name}Controller extends AdminController", "use Rore\Shared\Controller\AdminController;\n\nclass {$name}Controller extends AdminController"],
        ]
    );
}

// ─── CartController → Cart/Controller ────────────────────────────────────────
moveFile(
    "$base/src/Presentation/Controller/Site/CartController.php",
    "$base/src/Cart/Controller/CartController.php",
    'Rore\Presentation\Controller\Site',
    'Rore\Cart\Controller',
    [
        ["class CartController extends SiteController", "use Rore\Catalog\Controller\SiteController;\n\nclass CartController extends SiteController"],
    ]
);

// ─── ContactController → Contact/Controller/Site ─────────────────────────────
moveFile(
    "$base/src/Presentation/Controller/Site/ContactController.php",
    "$base/src/Contact/Controller/Site/ContactController.php",
    'Rore\Presentation\Controller\Site',
    'Rore\Contact\Controller\Site',
    [
        ["class ContactController extends SiteController", "use Rore\Catalog\Controller\SiteController;\n\nclass ContactController extends SiteController"],
    ]
);

// ─── MessageController → Contact/Controller/Admin ────────────────────────────
moveFile(
    "$base/src/Presentation/Controller/Admin/MessageController.php",
    "$base/src/Contact/Controller/Admin/MessageController.php",
    'Rore\Presentation\Controller\Admin',
    'Rore\Contact\Controller\Admin',
    [
        ["class MessageController extends AdminController", "use Rore\Shared\Controller\AdminController;\n\nclass MessageController extends AdminController"],
    ]
);

// ─── SearchController → Search/Controller ────────────────────────────────────
moveFile(
    "$base/src/Presentation/Controller/Site/SearchController.php",
    "$base/src/Search/Controller/SearchController.php",
    'Rore\Presentation\Controller\Site',
    'Rore\Search\Controller',
    [
        ["class SearchController extends SiteController", "use Rore\Catalog\Controller\SiteController;\n\nclass SearchController extends SiteController"],
    ]
);

// ─── ReservationController → Reservation/Controller/Admin ────────────────────
moveFile(
    "$base/src/Presentation/Controller/Admin/ReservationController.php",
    "$base/src/Reservation/Controller/Admin/ReservationController.php",
    'Rore\Presentation\Controller\Admin',
    'Rore\Reservation\Controller\Admin',
    [
        ["class ReservationController extends AdminController", "use Rore\Shared\Controller\AdminController;\n\nclass ReservationController extends AdminController"],
    ]
);

// ─── SettingsController → Settings/Controller/Admin ──────────────────────────
moveFile(
    "$base/src/Presentation/Controller/Admin/SettingsController.php",
    "$base/src/Settings/Controller/Admin/SettingsController.php",
    'Rore\Presentation\Controller\Admin',
    'Rore\Settings\Controller\Admin',
    [
        ["class SettingsController extends AdminController", "use Rore\Shared\Controller\AdminController;\n\nclass SettingsController extends AdminController"],
    ]
);

// ─── Étape 3 : Patcher les fichiers qui restent dans Presentation/ ────────────
echo "\n=== Patch Presentation restants ===\n";

// AuthController : LoginRateLimiter + Controller base
patchFile("$base/src/Presentation/Controller/Admin/AuthController.php", [
    ['use Rore\Presentation\Controller\Controller;',    'use Rore\Shared\Controller\Controller;'],
    ['use Rore\Presentation\Security\LoginRateLimiter;', 'use Rore\Shared\Security\LoginRateLimiter;'],
]);

// DashboardController : AdminController a changé de namespace
patchFile("$base/src/Presentation/Controller/Admin/DashboardController.php", [
    // AdminController était dans le même namespace, maintenant dans Shared
    ["class DashboardController extends AdminController", "use Rore\Shared\Controller\AdminController;\n\nclass DashboardController extends AdminController"],
]);

// LegalController, RobotsController, SitemapController : SiteController a changé
foreach (['Legal', 'Robots', 'Sitemap'] as $name) {
    patchFile("$base/src/Presentation/Controller/Site/{$name}Controller.php", [
        ["class {$name}Controller extends SiteController", "use Rore\Catalog\Controller\SiteController;\n\nclass {$name}Controller extends SiteController"],
    ]);
}

// ─── Étape 4 : Remplacements globaux de namespaces dans src/, tests/, templates/ ──
echo "\n=== Remplacements globaux ===\n";

$globalReplacements = [
    // Shared
    ['Rore\Presentation\Controller\Controller',   'Rore\Shared\Controller\Controller'],
    ['Rore\Presentation\Controller\Admin\AdminController', 'Rore\Shared\Controller\AdminController'],
    ['Rore\Presentation\Security\LoginRateLimiter', 'Rore\Shared\Security\LoginRateLimiter'],
    // Catalog/Seo
    ['Rore\Presentation\Seo\SlugResolver',         'Rore\Catalog\Seo\SlugResolver'],
    // Catalog controllers (SiteController base)
    ['Rore\Presentation\Controller\Site\SiteController', 'Rore\Catalog\Controller\SiteController'],
    // Catalog site controllers
    ['Rore\Presentation\Controller\Site\CategoryController', 'Rore\Catalog\Controller\Site\CategoryController'],
    ['Rore\Presentation\Controller\Site\ProductController',  'Rore\Catalog\Controller\Site\ProductController'],
    ['Rore\Presentation\Controller\Site\PackController',     'Rore\Catalog\Controller\Site\PackController'],
    ['Rore\Presentation\Controller\Site\TagController',      'Rore\Catalog\Controller\Site\TagController'],
    ['Rore\Presentation\Controller\Site\HomeController',     'Rore\Catalog\Controller\Site\HomeController'],
    // Catalog admin controllers
    ['Rore\Presentation\Controller\Admin\CategoryController', 'Rore\Catalog\Controller\Admin\CategoryController'],
    ['Rore\Presentation\Controller\Admin\ProductController',  'Rore\Catalog\Controller\Admin\ProductController'],
    ['Rore\Presentation\Controller\Admin\PackController',     'Rore\Catalog\Controller\Admin\PackController'],
    // Cart
    ['Rore\Presentation\Controller\Site\CartController',      'Rore\Cart\Controller\CartController'],
    // Contact
    ['Rore\Presentation\Controller\Site\ContactController',   'Rore\Contact\Controller\Site\ContactController'],
    ['Rore\Presentation\Controller\Admin\MessageController',  'Rore\Contact\Controller\Admin\MessageController'],
    // Search
    ['Rore\Presentation\Controller\Site\SearchController',    'Rore\Search\Controller\SearchController'],
    // Reservation
    ['Rore\Presentation\Controller\Admin\ReservationController', 'Rore\Reservation\Controller\Admin\ReservationController'],
    // Settings
    ['Rore\Presentation\Controller\Admin\SettingsController', 'Rore\Settings\Controller\Admin\SettingsController'],
];

foreach ($globalReplacements as [$old, $new]) {
    $n = 0;
    foreach (["$base/src", "$base/tests", "$base/templates"] as $dir) {
        $n += replaceGlobally($dir, $old, $new);
    }
    // public/index.php
    patchFile("$base/public/index.php", [[$old, $new]]);
    if ($n > 0) echo "  replaced [$n files] $old\n             → $new\n";
}

// ─── Étape 5 : Patch index.php (nouveau scan multi-répertoires) ───────────────
echo "\n=== Patch index.php ===\n";

$indexPath = "$base/public/index.php";
$index     = file_get_contents($indexPath);

$oldBlock = <<<'PHP'
$scanner = new \Rore\Framework\Http\RouteScanner();
$scanner->scan(BASE_PATH . '/src/Presentation/Controller', 'Rore\Presentation\Controller');
$routes = $scanner->getRoutes();

$router = $container->get(\Rore\Framework\Http\Router::class);
$router->loadRoutes($routes);

$container->get(\Rore\Framework\Http\UrlResolver::class)->loadRoutes('Rore\Presentation\Controller', $routes);
PHP;

$newBlock = <<<'PHP'
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
PHP;

if (str_contains($index, $oldBlock)) {
    $index = str_replace($oldBlock, $newBlock, $index);
    file_put_contents($indexPath, $index);
    echo "patch  public/index.php (scan multi-modules)\n";
} else {
    echo "WARN   bloc scan introuvable dans index.php, vérifier manuellement\n";
    echo "--- Attendu ---\n$oldBlock\n";
}

// ─── Étape 6 : Supprimer les répertoires vides ────────────────────────────────
echo "\n=== Nettoyage répertoires vides ===\n";
$candidates = [
    "$base/src/Presentation/Controller/Site",
    "$base/src/Presentation/Controller/Admin",
    "$base/src/Presentation/Controller",
    "$base/src/Presentation/Security",
    "$base/src/Presentation/Seo",
];
foreach ($candidates as $dir) {
    if (!is_dir($dir)) continue;
    $files = array_filter(
        iterator_to_array(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir))),
        fn($f) => $f->isFile()
    );
    if (count($files) === 0) {
        // rmdir non récursif : on laisse le soin au dev
        echo "EMPTY  $dir (peut être supprimé)\n";
    } else {
        echo "KEPT   $dir (" . count($files) . " fichier(s) restant(s))\n";
    }
}

echo "\n=== Terminé ===\n";
