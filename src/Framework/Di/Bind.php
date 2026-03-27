<?php

declare(strict_types=1);

namespace Rore\Framework\Di;

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

    /**
     * Vérifie que le type de retour déclaré de la closure est compatible
     * avec le type du paramètre sur lequel l'attribut est posé.
     *
     * - Param objet     → closure doit retourner la classe (ou sous-classe) ou array (pattern DI augmentation)
     * - Param scalaire  → closure doit retourner exactement le même type builtin
     * - Pas de return type déclaré → pas de validation possible, skip
     */
    public function validate(\ReflectionParameter $param): void
    {
        $returnType = (new \ReflectionFunction($this->resolver))->getReturnType();

        if ($returnType === null || !$returnType instanceof \ReflectionNamedType) {
            return;
        }

        $paramType = $param->getType();
        if (!$paramType instanceof \ReflectionNamedType) {
            return;
        }

        $returnName = $returnType->getName();
        $paramName  = $paramType->getName();

        if (!$paramType->isBuiltin()) {
            if ($returnName === 'array') {
                return;
            }
            if ($returnName !== $paramName && !is_a($returnName, $paramName, true)) {
                throw new \LogicException(
                    "#[Bind] sur \"\${$param->getName()}\" : la closure retourne \"{$returnName}\""
                    . " mais le paramètre attend \"{$paramName}\"."
                );
            }
            return;
        }

        if ($returnName !== $paramName) {
            throw new \LogicException(
                "#[Bind] sur \"\${$param->getName()}\" : la closure retourne \"{$returnName}\""
                . " mais le paramètre attend \"{$paramName}\"."
            );
        }
    }
}
