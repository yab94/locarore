<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Di;

use Rore\Application\Cart\CartSession;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Domain\Catalog\Repository\PackRepositoryInterface;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Domain\Reservation\Repository\ReservationRepositoryInterface;
use Rore\Domain\Settings\Repository\SettingsRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Infrastructure\Persistence\MySqlPackRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Infrastructure\Persistence\MySqlReservationRepository;
use Rore\Infrastructure\Persistence\MySqlSettingsRepository;
use Rore\Infrastructure\Storage\FileUploader;
use Rore\Infrastructure\Storage\PhpSessionStorage;

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
     * @param array<string, mixed> $config  tableau issu de app.ini (sections indexées)
     */
    public static function create(array $config): Container
    {
        $c = new Container();

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
            fn($c) => new FileUploader($config['upload']));

        // ── Storage : Session ──────────────────────────────────────────
        $c->bind(SessionStorageInterface::class,
            fn($c) => new PhpSessionStorage());

        // ── Application : session (via storage) ─────────────────────────
        $c->bind(CartSession::class,
            fn($c) => new CartSession($c->get(SessionStorageInterface::class)));

        return $c;
    }
}
