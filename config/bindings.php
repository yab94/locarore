<?php

declare(strict_types=1);

use RRB\Bootstrap\Config;

/** @var \RRB\Di\Container $container */
$container->register(RRB\Session\SessionInterface::class, fn($c) => $c->get(RRB\Session\PhpSession::class));
$container->register(Rore\Application\Auth\Port\AdminLoginRateLimiterInterface::class, fn($c) => $c->get(Rore\Infrastructure\Security\LoginRateLimiterAdapter::class));
$container->register(Rore\Application\Cart\Port\CartServiceInterface::class, fn($c) => $c->get(Rore\Application\Cart\Service\CartService::class));
$container->register(Rore\Application\Catalog\Port\PackRepositoryInterface::class, fn($c) => $c->get(Rore\Infrastructure\Persistence\MySqlPackRepositoryAdapter::class));
$container->register(Rore\Application\Catalog\Port\ProductRepositoryInterface::class, fn($c) => $c->get(Rore\Infrastructure\Persistence\MySqlProductRepositoryAdapter::class));
$container->register(Rore\Application\Reservation\Port\AvailabilityServiceInterface::class, fn($c) => $c->get(Rore\Application\Reservation\Service\AvailabilityService::class));
$container->register(Rore\Domain\Catalog\Service\PricingServiceInterface::class, fn($c) => $c->get(Rore\Domain\Catalog\Service\PricingService::class));
$container->register(Rore\Application\Catalog\Port\CategoryRepositoryInterface::class, fn($c) => $c->get(Rore\Infrastructure\Persistence\MySqlCategoryRepositoryAdapter::class));
$container->register(Rore\Application\Catalog\Port\SlugUniquenessServiceInterface::class, fn($c) => $c->get(Rore\Application\Catalog\Service\SlugUniquenessService::class));
$container->register(Rore\Application\Catalog\Port\TagRepositoryInterface::class, fn($c) => $c->get(Rore\Infrastructure\Persistence\MySqlTagRepositoryAdapter::class));
$container->register(Rore\Application\Catalog\Port\FileManagerInterface::class, fn($c) => $c->get(Rore\Infrastructure\File\FileManagerAdapter::class));
$container->register(Rore\Application\Reservation\Port\ReservationRepositoryInterface::class, fn($c) => $c->get(Rore\Infrastructure\Persistence\MySqlReservationRepositoryAdapter::class));
$container->register(Rore\Application\Catalog\Port\FileUploaderInterface::class, fn($c) => $c->get(Rore\Infrastructure\File\FileUploaderAdapter::class));
$container->register(Rore\Application\Catalog\Port\ImageManagerInterface::class, fn($c) => $c->get(Rore\Infrastructure\File\ImageManagerAdapter::class));
$container->register(Rore\Application\Contact\Port\ContactMessageRepositoryInterface::class, fn($c) => $c->get(Rore\Infrastructure\Persistence\MySqlContactMessageRepositoryAdapter::class));
$container->register(Rore\Application\Contact\Port\MailerInterface::class, fn($c) => $c->get(Rore\Infrastructure\Mail\SmtpMailerAdapter::class));
$container->register(Rore\Application\Search\Port\SearchRepositoryInterface::class, fn($c) => $c->get(Rore\Infrastructure\Persistence\MySqlSearchRepositoryAdapter::class));
$container->register(Rore\Application\Settings\Port\SettingsRepositoryInterface::class, fn($c) => $c->get(Rore\Infrastructure\Persistence\MySqlSettingsRepositoryAdapter::class));
$container->register(Rore\Application\Faq\Port\FaqRepositoryInterface::class, fn($c) => $c->get(Rore\Infrastructure\Persistence\MySqlFaqRepositoryAdapter::class));

$container->bind(Rore\Application\Auth\UseCase\AuthenticateAdminUseCase::class, 'adminPassword', fn(Config $cfg) => $cfg->getString('admin.password'));
$container->bind(Rore\Application\Auth\UseCase\AuthenticateAdminUseCase::class, 'sessionKey', fn(Config $cfg) => $cfg->getString('admin.session_key'));
$container->bind(Rore\Application\Auth\UseCase\IsAdminAuthenticatedUseCase::class, 'sessionKey', fn(Config $cfg) => $cfg->getString('admin.session_key'));
$container->bind(Rore\Application\Auth\UseCase\LogoutAdminUseCase::class, 'sessionKey', fn(Config $cfg) => $cfg->getString('admin.session_key'));
$container->bind(Rore\Application\Catalog\UseCase\UploadProductPhotoUseCase::class, 'maxWidth', fn(Config $cfg) => $cfg->getInt('upload.max_width'));
$container->bind(Rore\Application\Catalog\UseCase\UploadProductPhotoUseCase::class, 'maxHeight', fn(Config $cfg) => $cfg->getInt('upload.max_height'));
$container->bind(Rore\Infrastructure\Database\MysqlDatabase::class, 'host', fn(Config $cfg) => $cfg->getString('database.host'));
$container->bind(Rore\Infrastructure\Database\MysqlDatabase::class, 'port', fn(Config $cfg) => $cfg->getString('database.port'));
$container->bind(Rore\Infrastructure\Database\MysqlDatabase::class, 'name', fn(Config $cfg) => $cfg->getString('database.name'));
$container->bind(Rore\Infrastructure\Database\MysqlDatabase::class, 'charset', fn(Config $cfg) => $cfg->getString('database.charset'));
$container->bind(Rore\Infrastructure\Database\MysqlDatabase::class, 'user', fn(Config $cfg) => $cfg->getString('database.user'));
$container->bind(Rore\Infrastructure\Database\MysqlDatabase::class, 'password', fn(Config $cfg) => $cfg->getString('database.password'));
$container->bind(Rore\Infrastructure\File\FileManagerAdapter::class, 'basePath', fn(Config $cfg) => $cfg->getString('upload.base_path'));
$container->bind(Rore\Infrastructure\File\FileManagerAdapter::class, 'uploadPath', fn(Config $cfg) => $cfg->getString('upload.upload_path'));
$container->bind(Rore\Infrastructure\File\FileUploaderAdapter::class, 'basePath', fn(Config $cfg) => $cfg->getString('upload.base_path'));
$container->bind(Rore\Infrastructure\File\FileUploaderAdapter::class, 'uploadPath', fn(Config $cfg) => $cfg->getString('upload.upload_path'));
$container->bind(Rore\Infrastructure\File\FileUploaderAdapter::class, 'maxSize', fn(Config $cfg) => $cfg->getInt('upload.max_size'));
$container->bind(Rore\Infrastructure\File\FileUploaderAdapter::class, 'allowedTypes', fn(Config $cfg) => $cfg->getString('upload.allowed_types'));
$container->bind(Rore\Infrastructure\File\ImageManagerAdapter::class, 'basePath', fn(Config $cfg) => $cfg->getString('upload.base_path'));
$container->bind(Rore\Infrastructure\File\ImageManagerAdapter::class, 'uploadPath', fn(Config $cfg) => $cfg->getString('upload.upload_path'));
$container->bind(Rore\Infrastructure\Mail\SmtpMailerAdapter::class, 'host', fn(Config $cfg) => $cfg->getString('smtp.host'));
$container->bind(Rore\Infrastructure\Mail\SmtpMailerAdapter::class, 'port', fn(Config $cfg) => $cfg->getInt('smtp.port'));
$container->bind(Rore\Infrastructure\Mail\SmtpMailerAdapter::class, 'encryption', fn(Config $cfg) => $cfg->getString('smtp.encryption'));
$container->bind(Rore\Infrastructure\Mail\SmtpMailerAdapter::class, 'user', fn(Config $cfg) => $cfg->getString('smtp.user'));
$container->bind(Rore\Infrastructure\Mail\SmtpMailerAdapter::class, 'password', fn(Config $cfg) => $cfg->getString('smtp.password'));
$container->bind(Rore\Infrastructure\Mail\SmtpMailerAdapter::class, 'fromEmail', fn(Config $cfg) => $cfg->getString('smtp.from_email'));
$container->bind(Rore\Infrastructure\Mail\SmtpMailerAdapter::class, 'fromName', fn(Config $cfg) => $cfg->getString('smtp.from_name'));
$container->bind(Rore\Infrastructure\Security\LoginRateLimiterAdapter::class, 'maxAttempts', fn(Config $cfg) => $cfg->getInt('admin.login_attempts'));
$container->bind(Rore\Infrastructure\Security\LoginRateLimiterAdapter::class, 'lockoutSeconds', fn(Config $cfg) => $cfg->getInt('admin.lockout_seconds'));
$container->bind(Rore\Presentation\Seo\SlugResolver::class, 'siteUrl', fn(Config $cfg) => $cfg->getString('seo.site_url'));
$container->bind(Rore\Presentation\Seo\SlugResolver::class, 'categoriesBaseUrl', fn(Config $cfg) => $cfg->getString('seo.categories_base_url'));
$container->bind(Rore\Presentation\Seo\SlugResolver::class, 'productsBaseUrl', fn(Config $cfg) => $cfg->getString('seo.products_base_url'));
$container->bind(Rore\Presentation\Seo\SlugResolver::class, 'packsBaseUrl', fn(Config $cfg) => $cfg->getString('seo.packs_base_url'));
$container->bind(Rore\Presentation\Seo\SlugResolver::class, 'tagsBaseUrl', fn(Config $cfg) => $cfg->getString('seo.tags_base_url'));
