<?php

declare(strict_types=1);

namespace RRB\Di;

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
 *   les scalaires doivent être déclarés via register() ou bind().
 */
final class Container
{
    /** @var array<string, object> Instances déjà résolues */
    private array $instances = [];

    /** @var array<string, \Closure> Registre interne unique des resolvers */
    private array $resolvers = [];

    /** @var array<string, true> Services en cours de résolution (détection de cycles) */
    private array $resolving = [];

    /**
     * @param bool $debug Active les validations coûteuses (à désactiver en prod).
     */
    public function __construct(private readonly bool $debug = false) {
        $this->instances[Container::class] = $this;
    }

    /**
     * Déclare un binding DI.
     *
     * La Closure reçoit le Container et retourne l'instance. Elle contrôle la durée de vie :
     *   - Singleton : fn($c) => $c->get(Impl::class)   — get() met en cache
     *   - Transient : fn($c) => $c->make(Impl::class)  — make() ne met pas en cache
     *   - Instance  : fn()   => $monInstance            — retourne toujours le même objet
     *
     * @param string   $id      FQCN ou nom de service
     * @param \Closure $factory function(Container): object
     */
    public function register(string $id, \Closure $factory): void
    {
        $this->resolvers[$this->resolverKey($id)] = $factory;
    }

    /**
     * Déclare un resolver contextuel pour un paramètre précis d'une classe.
     *
     * Exemple:
     *   $container->bind(Foo::class, 'bar', fn(Config $c) => $c->getString('x.y'));
     */
    public function bind(string $class, string $parameter, \Closure $resolver): void
    {
        if ($this->debug) {
            $this->assertBindTarget($class, $parameter);
        }

        $this->resolvers[$this->parameterResolverKey($class, $parameter)] = $resolver;
    }

    /**
     * Résout un service nommé ou une classe, en construisant l'instance si nécessaire.
     */
    public function get(string $idOrClass): object
    {
        $resolver = $this->resolveResolver($idOrClass);
        if ($resolver !== null) {
            $this->guardCycle($idOrClass);
            $this->resolving[$idOrClass] = true;
            try {
                $instance = $resolver($this);
            } finally {
                unset($this->resolving[$idOrClass]);
            }
            return $instance;
        }

        if (isset($this->instances[$idOrClass])) {
            /** @var T */
            return $this->instances[$idOrClass];
        }

        $this->guardCycle($idOrClass);
        $this->resolving[$idOrClass] = true;
        try {
            $instance = $this->make($idOrClass);
        } finally {
            unset($this->resolving[$idOrClass]);
        }

        $this->instances[$idOrClass] = $instance;

        return $instance;
    }

    // ─────────────────────────────────────────────────────────────────────

    /**
     * Construit une nouvelle instance sans la mettre en cache,
     * en surchargeant certains paramètres du constructeur par nom.
     *
     * @template T of object
     * @param class-string<T>      $class
     * @param array<string, mixed> $overrides Surcharges par nom de paramètre
     * @return T
     */
    public function make(string $class, array $overrides = []): object
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

            // Override explicite par nom (via make())
            if (array_key_exists($param->getName(), $overrides)) {
                $args[] = $overrides[$param->getName()];
                continue;
            }

            // Binding contextuel classe+paramètre (déclaré hors code source)
            $bound = $this->resolveParameterBinding($ref->getName(), $param);
            if ($bound['matched']) {
                $args[] = $bound['value'];
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
                    . " Déclarez une factory via register()."
                );
            }
        }

        /** @var T */
        return $ref->newInstanceArgs($args);
    }

    private function resolveClosure(\Closure $resolver): mixed
    {
        $refFn       = new ReflectionFunction($resolver);
        $closureArgs = [];
        foreach ($refFn->getParameters() as $p) {
            $t = $p->getType();
            if ($t instanceof ReflectionNamedType && !$t->isBuiltin()) {
                $closureArgs[] = $this->get($t->getName());
            } elseif ($p->isDefaultValueAvailable()) {
                $closureArgs[] = $p->getDefaultValue();
            }
        }
        return $resolver(...$closureArgs);
    }

    /**
     * @return array{matched: bool, value?: mixed}
     */
    private function resolveParameterBinding(string $class, \ReflectionParameter $param): array
    {
        $resolver = $this->resolveParameterResolver($class, $param->getName());
        if ($resolver === null) {
            return ['matched' => false];
        }

        $value = $this->resolveClosure($resolver);

        if ($this->debug) {
            $this->assertParameterValueType($class, $param, $value);
        }

        return ['matched' => true, 'value' => $value];
    }

    private function resolveResolver(string $id): ?\Closure
    {
        return $this->resolvers[$this->resolverKey($id)] ?? null;
    }

    private function resolveParameterResolver(string $class, string $parameter): ?\Closure
    {
        return $this->resolvers[$this->parameterResolverKey($class, $parameter)] ?? null;
    }

    private function guardCycle(string $id): void
    {
        if (isset($this->resolving[$id])) {
            $chain = implode(' → ', array_keys($this->resolving)) . ' → ' . $id;
            throw new RuntimeException(
                "Container : dépendance circulaire détectée : {$chain}."
            );
        }
    }

    private function assertBindTarget(string $class, string $parameter): void
    {
        if (!class_exists($class)) {
            throw new RuntimeException(
                "Container : bind() — classe inconnue « {$class} »."
            );
        }

        $constructor = (new ReflectionClass($class))->getConstructor();

        if ($constructor === null) {
            throw new RuntimeException(
                "Container : bind() — « {$class} » n'a pas de constructeur."
            );
        }

        foreach ($constructor->getParameters() as $param) {
            if ($param->getName() === $parameter) {
                return;
            }
        }

        throw new RuntimeException(
            "Container : bind() — paramètre « \${$parameter} » introuvable dans le constructeur de « {$class} »."
        );
    }

    private function resolverKey(string $id): string
    {
        return 'id:' . $id;
    }

    private function parameterResolverKey(string $class, string $parameter): string
    {
        return 'param:' . $class . '::$' . $parameter;
    }

    private function assertParameterValueType(string $class, \ReflectionParameter $param, mixed $value): void
    {
        $type = $param->getType();
        if (!$type instanceof ReflectionNamedType) {
            return;
        }

        if ($value === null) {
            if ($type->allowsNull()) {
                return;
            }
            throw new RuntimeException(
                "Container : binding contextuel invalide pour {$class}::\${$param->getName()} (null non autorisé)."
            );
        }

        $typeName = $type->getName();
        if ($type->isBuiltin()) {
            $isValid = match ($typeName) {
                'int' => is_int($value),
                'float' => is_float($value),
                'string' => is_string($value),
                'bool' => is_bool($value),
                'array' => is_array($value),
                'mixed' => true,
                default => true,
            };

            if (!$isValid) {
                throw new RuntimeException(
                    "Container : binding contextuel invalide pour {$class}::\${$param->getName()}"
                    . " (attendu {$typeName}, reçu " . get_debug_type($value) . ")."
                );
            }

            return;
        }

        if (!$value instanceof $typeName) {
            throw new RuntimeException(
                "Container : binding contextuel invalide pour {$class}::\${$param->getName()}"
                . " (attendu {$typeName}, reçu " . get_debug_type($value) . ")."
            );
        }
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

                // Binding contextuel classe+paramètre (déclaré hors code source)
                $bound = $this->resolveParameterBinding($parent->getName(), $param);
                if ($bound['matched']) {
                    $parentDeps[] = $bound['value'];
                    continue;
                }

                if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
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