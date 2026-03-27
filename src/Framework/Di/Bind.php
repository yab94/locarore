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
 *       #[Bind('host', static function(Config $c): string { return $c->getString('db.host'); })]
 *       #[Bind('port', static function(Config $c): int    { return $c->getInt('db.port'); })]
 *       Database $db,
 *   ) {}
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
final class Bind
{
    public readonly ?string  $paramName;
    public readonly \Closure $resolver;

    public function __construct(string|\Closure $nameOrResolver, ?\Closure $resolver = null)
    {
        if (is_string($nameOrResolver)) {
            $this->paramName = $nameOrResolver;
            $this->resolver  = $resolver ?? throw new \LogicException(
                '#[Bind] avec un nom de paramètre requiert une closure en second argument.'
            );
        } else {
            $this->paramName = null;
            $this->resolver  = $nameOrResolver;
        }
    }

    /**
     * Vérifie que le type de retour déclaré de la closure est compatible
     * avec le type du paramètre sur lequel l'attribut est posé.
     *
     * - Param objet     → closure doit retourner une instance de la classe (ou sous-classe)
     * - Param scalaire  → closure doit retourner exactement le même type builtin
     * - Pas de return type déclaré → pas de validation possible, skip
     */
    public function validate(\ReflectionParameter $param): void
    {
        // Mode nommé : la closure résout un scalaire pour un param du constructeur cible,
        // pas pour le type du paramètre annoté — validation de type non applicable.
        if ($this->paramName !== null) {
            return;
        }
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
