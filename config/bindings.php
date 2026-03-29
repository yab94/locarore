<?php

declare(strict_types=1);

/** @var \RRB\Di\Container $container */
$container->bind(RRB\Session\SessionInterface::class, RRB\Session\PhpSession::class);
$container->bind(Rore\Application\Auth\Port\AdminLoginRateLimiterInterface::class, Rore\Infrastructure\Security\LoginRateLimiterAdapter::class);
$container->bind(Rore\Application\Cart\Port\CartServiceInterface::class, Rore\Application\Cart\Service\CartService::class);
$container->bind(Rore\Application\Catalog\Port\PackRepositoryInterface::class, Rore\Infrastructure\Persistence\MySqlPackRepositoryAdapter::class);
$container->bind(Rore\Application\Catalog\Port\ProductRepositoryInterface::class, Rore\Infrastructure\Persistence\MySqlProductRepositoryAdapter::class);
$container->bind(Rore\Application\Reservation\Port\AvailabilityServiceInterface::class, Rore\Application\Reservation\Service\AvailabilityService::class);
$container->bind(Rore\Domain\Catalog\Service\PricingServiceInterface::class, Rore\Domain\Catalog\Service\PricingService::class);
$container->bind(Rore\Application\Catalog\Port\CategoryRepositoryInterface::class, Rore\Infrastructure\Persistence\MySqlCategoryRepositoryAdapter::class);
$container->bind(Rore\Application\Catalog\Port\SlugUniquenessServiceInterface::class, Rore\Application\Catalog\Service\SlugUniquenessService::class);
$container->bind(Rore\Application\Catalog\Port\TagRepositoryInterface::class, Rore\Infrastructure\Persistence\MySqlTagRepositoryAdapter::class);
$container->bind(Rore\Application\Catalog\Port\FileManagerInterface::class, Rore\Infrastructure\File\FileManagerAdapter::class);
$container->bind(Rore\Application\Reservation\Port\ReservationRepositoryInterface::class, Rore\Infrastructure\Persistence\MySqlReservationRepositoryAdapter::class);
$container->bind(Rore\Application\Catalog\Port\FileUploaderInterface::class, Rore\Infrastructure\File\FileUploaderAdapter::class);
$container->bind(Rore\Application\Catalog\Port\ImageManagerInterface::class, Rore\Infrastructure\File\ImageManagerAdapter::class);
$container->bind(Rore\Application\Contact\Port\ContactMessageRepositoryInterface::class, Rore\Infrastructure\Persistence\MySqlContactMessageRepositoryAdapter::class);
$container->bind(Rore\Application\Contact\Port\MailerInterface::class, Rore\Infrastructure\Mail\SmtpMailerAdapter::class);
$container->bind(Rore\Application\Search\Port\SearchRepositoryInterface::class, Rore\Infrastructure\Persistence\MySqlSearchRepositoryAdapter::class);
$container->bind(Rore\Application\Settings\Port\SettingsRepositoryInterface::class, Rore\Infrastructure\Persistence\MySqlSettingsRepositoryAdapter::class);

$container->bindParameter(Rore\Application\Auth\UseCase\AuthenticateAdminUseCase::class, 'adminPassword', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('admin.password'));
$container->bindParameter(Rore\Application\Auth\UseCase\AuthenticateAdminUseCase::class, 'sessionKey', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('admin.session_key'));
$container->bindParameter(Rore\Application\Auth\UseCase\IsAdminAuthenticatedUseCase::class, 'sessionKey', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('admin.session_key'));
$container->bindParameter(Rore\Application\Auth\UseCase\LogoutAdminUseCase::class, 'sessionKey', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('admin.session_key'));
$container->bindParameter(Rore\Application\Catalog\UseCase\UploadProductPhotoUseCase::class, 'maxWidth', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getInt('upload.max_width'));
$container->bindParameter(Rore\Application\Catalog\UseCase\UploadProductPhotoUseCase::class, 'maxHeight', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getInt('upload.max_height'));
$container->bindParameter(Rore\Infrastructure\Database\MysqlDatabase::class, 'host', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('database.host'));
$container->bindParameter(Rore\Infrastructure\Database\MysqlDatabase::class, 'port', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('database.port'));
$container->bindParameter(Rore\Infrastructure\Database\MysqlDatabase::class, 'name', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('database.name'));
$container->bindParameter(Rore\Infrastructure\Database\MysqlDatabase::class, 'charset', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('database.charset'));
$container->bindParameter(Rore\Infrastructure\Database\MysqlDatabase::class, 'user', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('database.user'));
$container->bindParameter(Rore\Infrastructure\Database\MysqlDatabase::class, 'password', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('database.password'));
$container->bindParameter(Rore\Infrastructure\File\FileManagerAdapter::class, 'basePath', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('upload.base_path'));
$container->bindParameter(Rore\Infrastructure\File\FileManagerAdapter::class, 'uploadPath', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('upload.upload_path'));
$container->bindParameter(Rore\Infrastructure\File\FileUploaderAdapter::class, 'basePath', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('upload.base_path'));
$container->bindParameter(Rore\Infrastructure\File\FileUploaderAdapter::class, 'uploadPath', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('upload.upload_path'));
$container->bindParameter(Rore\Infrastructure\File\FileUploaderAdapter::class, 'maxSize', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getInt('upload.max_size'));
$container->bindParameter(Rore\Infrastructure\File\FileUploaderAdapter::class, 'allowedTypes', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('upload.allowed_types'));
$container->bindParameter(Rore\Infrastructure\File\ImageManagerAdapter::class, 'basePath', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('upload.base_path'));
$container->bindParameter(Rore\Infrastructure\File\ImageManagerAdapter::class, 'uploadPath', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('upload.upload_path'));
$container->bindParameter(Rore\Infrastructure\Mail\SmtpMailerAdapter::class, 'host', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('smtp.host'));
$container->bindParameter(Rore\Infrastructure\Mail\SmtpMailerAdapter::class, 'port', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getInt('smtp.port'));
$container->bindParameter(Rore\Infrastructure\Mail\SmtpMailerAdapter::class, 'encryption', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('smtp.encryption'));
$container->bindParameter(Rore\Infrastructure\Mail\SmtpMailerAdapter::class, 'user', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('smtp.user'));
$container->bindParameter(Rore\Infrastructure\Mail\SmtpMailerAdapter::class, 'password', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('smtp.password'));
$container->bindParameter(Rore\Infrastructure\Mail\SmtpMailerAdapter::class, 'fromEmail', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('smtp.from_email'));
$container->bindParameter(Rore\Infrastructure\Mail\SmtpMailerAdapter::class, 'fromName', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('smtp.from_name'));
$container->bindParameter(Rore\Infrastructure\Security\LoginRateLimiterAdapter::class, 'maxAttempts', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getInt('admin.login_attempts'));
$container->bindParameter(Rore\Infrastructure\Security\LoginRateLimiterAdapter::class, 'lockoutSeconds', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getInt('admin.lockout_seconds'));
$container->bindParameter(Rore\Presentation\Seo\SlugResolver::class, 'siteUrl', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('seo.site_url'));
$container->bindParameter(Rore\Presentation\Seo\SlugResolver::class, 'categoriesBaseUrl', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('seo.categories_base_url'));
$container->bindParameter(Rore\Presentation\Seo\SlugResolver::class, 'productsBaseUrl', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('seo.products_base_url'));
$container->bindParameter(Rore\Presentation\Seo\SlugResolver::class, 'packsBaseUrl', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('seo.packs_base_url'));
$container->bindParameter(Rore\Presentation\Seo\SlugResolver::class, 'tagsBaseUrl', fn(\RRB\Bootstrap\Config $cfg) => $cfg->getString('seo.tags_base_url'));
