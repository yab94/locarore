<?php

declare(strict_types=1);

namespace RRB\Di;

use Attribute;

/**
 * Raccourci DI : résout un paramètre en récupérant un adapter (classe concrète) depuis le container.
 *
 * @example
 *   #[BindAdapter(PhpSession::class)]
 *   SessionInterface $session,
 *
 *   #[BindAdapter(MySqlCategoryRepository::class)]
 *   CategoryRepositoryInterface $repository,
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class BindAdapter
{
    public function __construct(public readonly string $adapterClass) {}
}
