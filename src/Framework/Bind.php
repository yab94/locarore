<?php

declare(strict_types=1);

namespace Rore\Framework;

use Attribute;

/**
 * Attribut DI : fournit les arguments scalaires d'un constructeur
 * via une closure auto-wirée par le container.
 *
 * Clé de cache = FQCN + md5(serialize(résultat de la closure))
 * → deux #[Bind] produisant des résultats différents donnent
 *   deux instances distinctes (ex: connexion principale vs réplique).
 *
 * @example
 *   public function __construct(
 *       #[Bind(static function(Config $c) { return $c->getArray('database'); })] Database $db,
 *   ) {}
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class Bind
{
    public function __construct(public readonly \Closure $resolver) {}
}
