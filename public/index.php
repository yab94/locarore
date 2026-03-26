<?php

declare(strict_types=1);

// ─── Constante racine ─────────────────────────────────────────────────────────
define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/src/Framework/Autoloader.php';

\Rore\Framework\Autoloader::register('Rore\\', BASE_PATH);
\Rore\Framework\Env::load(BASE_PATH);

$config = new \Rore\Framework\Config(['BASE_PATH' => BASE_PATH]);
$config->parseIni(BASE_PATH . '/config/default.ini');
$config->parseIni(BASE_PATH . '/config/' . $config->getString('app.env') . '.ini');

\Rore\Framework\PhpRuntime::apply($config->getArray('php') ?? []);

// ─── Conteneur DI ──────────────────────────────────────────────────────────
$container = new \Rore\Framework\Container();
$container->bind(\Rore\Framework\Config::class, $config);

// ── Framework ────────────────────────────────────────────────────────────────
$container->bind(\Rore\Framework\SessionStorageInterface::class,   \Rore\Framework\PhpSessionStorage::class);
$container->bind(\Rore\Framework\CsrfTokenManagerInterface::class, \Rore\Framework\CsrfTokenManager::class);
$container->bind(\Rore\Framework\MailerInterface::class,           \Rore\Framework\SmtpMailer::class);
$container->bind(\Rore\Framework\FileManagerInterface::class, fn() => new \Rore\Framework\FileUploader(
    uploadDir:    BASE_PATH . '/public' . $config->getString('upload.upload_path'),
    maxSize:      (int) $config->getString('upload.max_size'),
    allowedTypes: $config->getString('upload.allowed_types'),
));

// ── Database ────────────────────────────────────────────────────────────────
$container->bind(\Rore\Framework\Database::class, fn() => new \Rore\Framework\Database(...$config->getArray('database')));

// ── Repositories ─────────────────────────────────────────────────────────────
$container->bind(\Rore\Domain\Catalog\Repository\CategoryRepositoryInterface::class,       \Rore\Infrastructure\Persistence\MySqlCategoryRepository::class);
$container->bind(\Rore\Domain\Catalog\Repository\ProductRepositoryInterface::class,        \Rore\Infrastructure\Persistence\MySqlProductRepository::class);
$container->bind(\Rore\Domain\Catalog\Repository\PackRepositoryInterface::class,           \Rore\Infrastructure\Persistence\MySqlPackRepository::class);
$container->bind(\Rore\Domain\Catalog\Repository\TagRepositoryInterface::class,            \Rore\Infrastructure\Persistence\MySqlTagRepository::class);
$container->bind(\Rore\Domain\Reservation\Repository\ReservationRepositoryInterface::class,\Rore\Infrastructure\Persistence\MySqlReservationRepository::class);
$container->bind(\Rore\Domain\Settings\Repository\SettingsRepositoryInterface::class,      \Rore\Infrastructure\Persistence\MySqlSettingsRepository::class);
$container->bind(\Rore\Domain\Contact\Repository\ContactMessageRepositoryInterface::class, \Rore\Infrastructure\Persistence\MySqlContactMessageRepository::class);
$container->bind(\Rore\Domain\Catalog\Repository\SearchRepositoryInterface::class,        \Rore\Infrastructure\Persistence\MySqlSearchRepository::class);

// ── Services spécifiques ─────────────────────────────────────────────────────
$container->bind(\Rore\Presentation\Seo\SlugResolver::class, fn() => new \Rore\Presentation\Seo\SlugResolver(
    siteUrl: $config->getString('seo.site_url'),
    categoriesBaseUrl: $config->getString('seo.categories_base_url'),
    productsBaseUrl: $config->getString('seo.products_base_url'),
    tagsBaseUrl: $config->getString('seo.tags_base_url'),
));

// ─── Router + UrlResolver ──────────────────────────────────────────────────
$scanner = new \Rore\Framework\RouteScanner();
$scanner->scan(BASE_PATH . '/src/Presentation/Controller', 'Rore\Presentation\Controller');
$routes = $scanner->getRoutes();

$router = $container->get(\Rore\Framework\Router::class);
$router->loadRoutes($routes);

$container->get(\Rore\Framework\UrlResolver::class)->loadRoutes('Rore\Presentation\Controller', $routes);

// ─── Dispatch ──────────────────────────────────────────────────────────────
$router->dispatch();
