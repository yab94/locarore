<?php

declare(strict_types=1);

namespace Rore\Framework\Di;

use ReflectionClass;
use ReflectionFunction;
use ReflectionNamedType;
use RuntimeException;

/**
 * Conteneur DI minimaliste avec auto-wiring par réflexion.
 *
 * - Résout récursivement les dépendances du constructeur
 * - Stocke les instances (comportement singleton par défaut)
 * - Ne gère que les dépendances typées par classe/interface ;
 *   les scalaires doivent être déclarés via bind()
 */
final class Container
{
    /** @var array<string, object> Instances déjà résolues */
    private array $instances = [];

    /** @var array<string, callable> Factories déclarées explicitement */
    private array $bindings = [];

    public function __construct() {
        $this->instances[Container::class] = $this;
    }

    /**
     * Déclare une factory pour un type donné.
     * Utile pour les interfaces ou les classes avec des arguments scalaires.
     *
     * @param string             $abstract Nom de classe ou d'interface (FQCN)
     * @param object|string      $factory  Closure(Container): object, FQCN de la classe concrète, ou instance déjà construite
     */
    public function bind(string $abstract, object|string $factory): void
    {
        if (!$factory instanceof \Closure && is_object($factory)) {
            $this->instances[$abstract] = $factory;
            return;
        }
        if (is_string($factory)) {
            $factory = fn($c) => $c->get($factory);
        }
        $this->bindings[$abstract] = $factory;
    }

    /**
     * Résout une dépendance, en construisant l'instance si nécessaire.
     *
     * @template T of object
     * @param class-string<T> $abstract
     * @return T
     */
    public function get(string $abstract): object
    {
        if (isset($this->instances[$abstract])) {
            /** @var T */
            return $this->instances[$abstract];
        }

        $instance = isset($this->bindings[$abstract])
            ? ($this->bindings[$abstract])($this)
            : $this->build($abstract);

        $this->instances[$abstract] = $instance;

        return $instance;
    }

    // ─────────────────────────────────────────────────────────────────────

    /**
     * Construit une instance en résolvant ses dépendances par réflexion.
     *
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function build(string $class): object
    {
        $ref = new ReflectionClass($class);

        if (!$ref->isInstantiable()) {
            throw new RuntimeException(
                "Container : impossible d'instancier « {$class} » (abstract, interface ou non-public)."
            );
        }

        $constructor = $ref->getConstructor();

        if ($constructor === null) {
            return $ref->newInstance();
        }

        $args = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();

            // Paramètre variadic : résoudre TOUTES les deps de la chaîne d'héritage.
            // On part de la classe qui DÉCLARE ce constructeur (pas de la classe buildée),
            // sinon une classe héritant d'un constructeur variadic aurait ses params résolus en double.
            if ($param->isVariadic()) {
                $declaringClass = new ReflectionClass($constructor->getDeclaringClass()->getName());
                $parentArgs = $this->resolveParentDependencies($declaringClass);
                array_push($args, ...$parentArgs);
                continue;
            }

            // Paramètre annoté #[Bind] → closure auto-wirée fournit les args
            $fromAttrs = $param->getAttributes(Bind::class);
            if ($fromAttrs !== [] && $type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                /** @var Bind $from */
                $from   = $fromAttrs[0]->newInstance();
                $args[] = $this->resolveFromAttribute($from, $type->getName());
                continue;
            }

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $args[] = $this->get($type->getName());
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new RuntimeException(
                    "Container : impossible de résoudre le paramètre « \${$param->getName()} »"
                    . " du constructeur de « {$class} »."
                    . " Déclarez une factory via bind()."
                );
            }
        }

        /** @var T */
        return $ref->newInstanceArgs($args);
    }

    /**
     * Résout un paramètre annoté #[Bind] :
     * - appelle la closure avec ses dépendances auto-wirées
     * - construit (ou récupère depuis le cache) l'instance cible
     *
     * Clé de cache = FQCN + ':' + md5(serialize(args))
     * → deux #[Bind] produisant des résultats différents donnent
     *   deux instances distinctes (ex: connexion principale vs réplique).
     */
    private function resolveFromAttribute(Bind $from, string $className): object
    {
        // 1. Auto-wirer les dépendances de la closure
        $refFn      = new ReflectionFunction($from->resolver);
        $closureArgs = [];
        foreach ($refFn->getParameters() as $p) {
            $t = $p->getType();
            if ($t instanceof ReflectionNamedType && !$t->isBuiltin()) {
                $closureArgs[] = $this->get($t->getName());
            } elseif ($p->isDefaultValueAvailable()) {
                $closureArgs[] = $p->getDefaultValue();
            }
        }

        // 2. Appeler la closure → tableau d'args nommés pour le constructeur cible
        $constructorArgs = ($from->resolver)(...$closureArgs);

        // 3. Clé de cache : FQCN + empreinte des args
        $cacheKey = $className . ':' . md5(serialize($constructorArgs));

        if (isset($this->instances[$cacheKey])) {
            return $this->instances[$cacheKey];
        }

        // 4. Construire l'instance avec named args spread (PHP 8+)
        $instance = (new ReflectionClass($className))->newInstance(...$constructorArgs);
        $this->instances[$cacheKey] = $instance;

        return $instance;
    }

    /**
     * Résout récursivement toutes les dépendances de la chaîne d'héritage.
     * Remonte de parent en parent jusqu'à ne plus avoir de constructeur.
     *
     * Les paramètres déjà collectés dans un niveau plus proche (feuille)
     * sont dédupliqués par nom, évitant les collisions quand un param
     * ($urlResolver, etc.) apparaît dans plusieurs niveaux d'héritage.
     *
     * @return array Liste des instances résolues (parent direct → ancêtres)
     */
    private function resolveParentDependencies(ReflectionClass $childClass): array
    {
        $allDeps   = [];
        $seenNames = [];   // noms de paramètres déjà collectés dans un niveau plus proche
        $current   = $childClass;

        while ($parent = $current->getParentClass()) {
            $constructor = $parent->getConstructor();

            if (!$constructor) {
                break;
            }

            $parentDeps = [];
            foreach ($constructor->getParameters() as $param) {
                // Ne pas résoudre les variadics du parent (sinon récursion infinie)
                if ($param->isVariadic()) {
                    break;
                }

                // Ignorer si un niveau plus proche a déjà déclaré ce nom de paramètre
                // (ex: $urlResolver dans Presentation\Controller ET Framework\Controller)
                $name = $param->getName();
                if (isset($seenNames[$name])) {
                    continue;
                }
                $seenNames[$name] = true;

                $type = $param->getType();

                $fromAttrs = $param->getAttributes(Bind::class);
                if ($fromAttrs !== [] && $type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                    /** @var Bind $from */
                    $from = $fromAttrs[0]->newInstance();
                    $parentDeps[] = $this->resolveFromAttribute($from, $type->getName());
                } elseif ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                    $parentDeps[] = $this->get($type->getName());
                } elseif ($param->isDefaultValueAvailable()) {
                    $parentDeps[] = $param->getDefaultValue();
                }
            }

            array_push($allDeps, ...$parentDeps);

            $current = $parent;
        }

        return $allDeps;
    }
}