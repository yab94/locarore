<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Di;

use ReflectionClass;
use ReflectionNamedType;
use Rore\Infrastructure\Config\Config;
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

    public function __construct(Config $config) {
        $this->instance(Config::class, $config);
        foreach ($config->getArrayParam('di.bind') ?? [] as $abstract => $concrete) {
            $this->bind($abstract, fn($c) => $c->get($concrete));
        }
    }

    /**
     * Déclare une factory pour un type donné.
     * Utile pour les interfaces ou les classes avec des arguments scalaires.
     *
     * @param string   $abstract Nom de classe ou d'interface (FQCN)
     * @param callable $factory  function(Container): object
     */
    public function bind(string $abstract, callable $factory): void
    {
        $this->bindings[$abstract] = $factory;
    }

    /**
     * Enregistre une instance déjà construite.
     */
    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
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
}
