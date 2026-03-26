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
$container->instance(\Rore\Framework\Config::class, $config);

// ── Framework ────────────────────────────────────────────────────────────────
$container->bind(\Rore\Framework\SessionStorageInterface::class,   fn($c) => $c->get(\Rore\Framework\PhpSessionStorage::class));
$container->bind(\Rore\Framework\CsrfTokenManagerInterface::class, fn($c) => $c->get(\Rore\Framework\CsrfTokenManager::class));
$container->bind(\Rore\Framework\MailerInterface::class,           fn($c) => $c->get(\Rore\Framework\SmtpMailer::class));
$container->bind(\Rore\Framework\FileManagerInterface::class, function($c) {
    $cfg = $c->get(\Rore\Framework\Config::class);
    return new \Rore\Framework\FileUploader(
        uploadDir:    $cfg->getString('app.root_dir') . '/public' . $cfg->getString('upload.upload_path'),
        maxSize:      (int) $cfg->getString('upload.max_size'),
        allowedTypes: array_map('trim', explode(',', $cfg->getString('upload.allowed_types'))),
    );
});

// ── Repositories ─────────────────────────────────────────────────────────────
$container->bind(\Rore\Domain\Catalog\Repository\CategoryRepositoryInterface::class,       fn($c) => $c->get(\Rore\Infrastructure\Persistence\MySqlCategoryRepository::class));
$container->bind(\Rore\Domain\Catalog\Repository\ProductRepositoryInterface::class,        fn($c) => $c->get(\Rore\Infrastructure\Persistence\MySqlProductRepository::class));
$container->bind(\Rore\Domain\Catalog\Repository\PackRepositoryInterface::class,           fn($c) => $c->get(\Rore\Infrastructure\Persistence\MySqlPackRepository::class));
$container->bind(\Rore\Domain\Catalog\Repository\TagRepositoryInterface::class,            fn($c) => $c->get(\Rore\Infrastructure\Persistence\MySqlTagRepository::class));
$container->bind(\Rore\Domain\Reservation\Repository\ReservationRepositoryInterface::class,fn($c) => $c->get(\Rore\Infrastructure\Persistence\MySqlReservationRepository::class));
$container->bind(\Rore\Domain\Settings\Repository\SettingsRepositoryInterface::class,      fn($c) => $c->get(\Rore\Infrastructure\Persistence\MySqlSettingsRepository::class));
$container->bind(\Rore\Domain\Contact\Repository\ContactMessageRepositoryInterface::class, fn($c) => $c->get(\Rore\Infrastructure\Persistence\MySqlContactMessageRepository::class));
$container->bind(\Rore\Domain\Catalog\Repository\SearchRepositoryInterface::class,        fn($c) => $c->get(\Rore\Infrastructure\Persistence\MySqlSearchRepository::class));

// ─── Router + UrlResolver ──────────────────────────────────────────────────
$scanner = new \Rore\Framework\RouteScanner();
$scanner->scan(
    baseDir:       BASE_PATH . '/src/Presentation/Controller',
    baseNamespace: 'Rore\Presentation\Controller',
);
$routes = $scanner->getRoutes();

$router = $container->get(\Rore\Framework\Router::class);
$router->loadRoutes($routes);

$container->get(\Rore\Framework\UrlResolver::class)->loadRoutes($routes);

// ─── Dispatch ──────────────────────────────────────────────────────────────
$router->dispatch();
