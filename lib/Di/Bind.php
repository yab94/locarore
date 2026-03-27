<?php

declare(strict_types=1);

namespace RRB\Di;

use Attribute;

/**
 * Attribut DI : fournit la valeur d'un paramètre via une closure auto-wirée.
 *
 * Règle unique : la closure reçoit ses dépendances via DI et DOIT retourner
 * exactement le même type que le paramètre sur lequel l'attribut est posé.
 *
 * @example
 *   public function __construct(
 *       #[Bind(static fn(Config $c): string => $c->getString('seo.site_url'))]
 *       private string $siteUrl,
 *
 *       #[Bind(static fn(Config $c, Session $s): RateLimiter => new RateLimiter(
 *           session: $s, key: 'login', maxAttempts: $c->getInt('admin.login_attempts'),
 *       ))]
 *       private RateLimiter $rateLimiter,
 *   ) {}
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class Bind
{
    public function __construct(public readonly \Closure $resolver) {}

    /**
     * Vérifie que le type de retour déclaré de la closure correspond
     * au type du paramètre annoté. Skip si pas de return type déclaré.
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

        $compatible = $returnName === $paramName
            || (!$paramType->isBuiltin() && is_a($returnName, $paramName, true));

        if (!$compatible) {
            throw new \LogicException(
                "#[Bind] sur \"\${$param->getName()}\" : la closure retourne \"{$returnName}\""
                . " mais le paramètre attend \"{$paramName}\"."
            );
        }
    }
}
