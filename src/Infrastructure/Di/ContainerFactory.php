<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Di;

use Rore\Application\Cart\CartSession;
use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Settings\SettingsServiceInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Domain\Catalog\Repository\PackRepositoryInterface;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Domain\Reservation\Repository\ReservationRepositoryInterface;
use Rore\Domain\Settings\Repository\SettingsRepositoryInterface;
use Rore\Infrastructure\Config\Config;
use Rore\Infrastructure\Config\SettingsService;
use Rore\Infrastructure\Security\CsrfTokenManager;
use Rore\Infrastructure\Http\HttpRequest;
use Rore\Infrastructure\Http\HttpResponse;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Infrastructure\Persistence\MySqlPackRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Infrastructure\Persistence\MySqlReservationRepository;
use Rore\Infrastructure\Persistence\MySqlSettingsRepository;
use Rore\Infrastructure\Storage\FileUploader;
use Rore\Infrastructure\Storage\PhpSessionStorage;
use Rore\Presentation\Http\RequestInterface;
use Rore\Presentation\Http\ResponseInterface;

/**
 * Composition root : assemble le graphe d'objets de l'application.
 *
 * Toute décision d'implémentation (quelle classe concrète pour quelle
 * interface) est centralisée ici, dans Infrastructure — la seule couche
 * qui connaît les détails techniques.
 */
final class ContainerFactory
{
    /**
     * @param Config $config
     */
    public static function create(Config $config): Container
    {
        $c = new Container();

        // ── Config (instance partagée) ────────────────────────────────
        $c->instance(Config::class, $config);

        // ── Repositories : Interface → MySQL ────────────────────────────
        $c->bind(CategoryRepositoryInterface::class,
            fn($c) => $c->get(MySqlCategoryRepository::class));

        $c->bind(ProductRepositoryInterface::class,
            fn($c) => $c->get(MySqlProductRepository::class));

        $c->bind(PackRepositoryInterface::class,
            fn($c) => $c->get(MySqlPackRepository::class));

        $c->bind(ReservationRepositoryInterface::class,
            fn($c) => $c->get(MySqlReservationRepository::class));

        $c->bind(SettingsRepositoryInterface::class,
            fn($c) => $c->get(MySqlSettingsRepository::class));

        // ── Infrastructure scalaire (config-dépendante) ─────────────────
        $c->bind(FileUploader::class,
            fn($c) => new FileUploader($config->getArrayParam('upload')));

        // ── Storage : Session ──────────────────────────────────────────
        $c->bind(SessionStorageInterface::class,
            fn($c) => new PhpSessionStorage());

        // ── Security : CSRF (port → adapter) ───────────────────────────
        $c->bind(CsrfTokenManagerInterface::class,
            fn($c) => $c->get(CsrfTokenManager::class));

        // ── Application : Settings (port → adapter) ────────────────────
        $c->bind(SettingsServiceInterface::class,
            fn($c) => $c->get(SettingsService::class));

        // ── HTTP : Request/Response (ports → adapters) ─────────────────
        $c->bind(RequestInterface::class,
            fn($c) => $c->get(HttpRequest::class));

        $c->bind(ResponseInterface::class,
            fn($c) => $c->get(HttpResponse::class));

        // ── Application : session (via storage) ─────────────────────────
        $c->bind(CartSession::class,
            fn($c) => new CartSession($c->get(SessionStorageInterface::class)));

        return $c;
    }
}
