<?php

declare(strict_types=1);

namespace Rore\Framework;

use Attribute;

/**
 * Attribut DI : fournit les arguments scalaires d'un constructeur
 * via une closure auto-wirée par le container.
 *
 * Clé de cache = FQCN + md5(serialize(résultat de la closure))
 * → deux #[From] produisant des résultats différents donnent
 *   deux instances distinctes en cache (ex: connexions multi-DB).
 *
 * @example
 *   public function __construct(
 *       #[From(static function(Config $c) { return $c->getArray('database'); })] Database $db,
 *   ) {}
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class From
{
    public function __construct(public readonly \Closure $resolver) {}
}
