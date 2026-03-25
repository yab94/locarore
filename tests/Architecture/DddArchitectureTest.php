<?php

declare(strict_types=1);

/**
 * Vérifie les règles de dépendances inter-couches DDD.
 *
 * Règles :
 *   Presentation\Controller  →  Application, Presentation
 *   Application              →  Application, Domain
 *   Domain                   →  Domain
 *   Infrastructure           →  Domain, Infrastructure, Application
 *                               (les adaptateurs Infrastructure implémentent des ports Application)
 *
 * Contrainte supplémentaire :
 *   Application ne contient QUE des UseCases (*UseCase) et des ports (interfaces).
 */
final class DddArchitectureTest
{
    // ─── Règles ──────────────────────────────────────────────────────────────
    // Ordre : du plus spécifique au plus général (premier match gagne).
    private const RULES = [
        'Rore\Presentation\Controller' => ['Rore\Application', 'Rore\Presentation'],
        'Rore\Application'             => ['Rore\Application', 'Rore\Domain'],
        'Rore\Domain'                  => ['Rore\Domain'],
        'Rore\Infrastructure'          => ['Rore\Domain', 'Rore\Infrastructure', 'Rore\Application'],
    ];

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function findPhpFiles(string $dir): array
    {
        $files = [];
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($it as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
        return $files;
    }

    private function getClassName(string $file): ?string
    {
        $content = file_get_contents($file);
        if (!preg_match('/^namespace\s+([\w\\\\]+)\s*;/m', $content, $ns)) return null;
        if (!preg_match('/^(?:abstract\s+|final\s+|readonly\s+)*(?:class|interface|trait|enum)\s+(\w+)/m', $content, $cl)) return null;
        return $ns[1] . '\\' . $cl[1];
    }

    private function matchRule(string $class): ?array
    {
        foreach (self::RULES as $prefix => $allowed) {
            if (str_starts_with($class, $prefix . '\\') || $class === $prefix) {
                return $allowed;
            }
        }
        return null;
    }

    private function isAllowed(string $type, array $allowedPrefixes): bool
    {
        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($type, $prefix . '\\') || $type === $prefix) {
                return true;
            }
        }
        return false;
    }

    private function collectViolations(): array
    {
        $violations = [];

        foreach ($this->findPhpFiles(BASE_PATH . '/src') as $file) {
            $className = $this->getClassName($file);
            if ($className === null) continue;

            $allowedPrefixes = $this->matchRule($className);
            if ($allowedPrefixes === null) continue;

            try {
                require_once $file;
                if (!class_exists($className) && !interface_exists($className) && !trait_exists($className)) continue;
                $ref = new ReflectionClass($className);
            } catch (Throwable) {
                continue;
            }

            if ($ref->isInterface() || $ref->isTrait() || $ref->isAbstract()) continue;

            $constructor = $ref->getConstructor();
            if ($constructor === null) continue;

            foreach ($constructor->getParameters() as $param) {
                if ($param->isVariadic()) continue;

                $type = $param->getType();
                if ($type === null || !($type instanceof ReflectionNamedType)) continue;
                if ($type->isBuiltin()) continue;

                $typeName = $type->getName();
                if (!str_starts_with($typeName, 'Rore\\')) continue;

                if (!$this->isAllowed($typeName, $allowedPrefixes)) {
                    $violations[] = sprintf(
                        "%s::__construct() — \$%s : %s\n   → autorisé : %s",
                        $className,
                        $param->getName(),
                        $typeName,
                        implode(', ', array_map(fn($p) => str_replace('Rore\\', '', $p), $allowedPrefixes)),
                    );
                }
            }
        }

        return $violations;
    }

    // ─── Tests ───────────────────────────────────────────────────────────────

    public function testNoDddLayerViolations(): void
    {
        $violations = $this->collectViolations();

        Assert::equals(
            0,
            count($violations),
            "\n\nViolations d'architecture DDD détectées :\n\n" . implode("\n\n", $violations)
        );
    }

    public function testApplicationOnlyUseCasesAndPorts(): void
    {
        $violations = [];

        foreach ($this->findPhpFiles(BASE_PATH . '/src/Application') as $file) {
            $className = $this->getClassName($file);
            if ($className === null) continue;

            try {
                require_once $file;
                if (!class_exists($className) && !interface_exists($className)) continue;
                $ref = new ReflectionClass($className);
            } catch (Throwable) {
                continue;
            }

            // Les interfaces (ports) et les abstraits sont toujours OK
            if ($ref->isInterface() || $ref->isAbstract()) continue;

            // Les classes concrètes doivent se terminer par UseCase
            $shortName = $ref->getShortName();
            if (!str_ends_with($shortName, 'UseCase')) {
                $violations[] = sprintf(
                    '%s — classe concrète non-UseCase en Application (renommer en *UseCase ou déplacer en Infrastructure)',
                    $className,
                );
            }
        }

        Assert::equals(
            0,
            count($violations),
            "\n\nApplication doit uniquement contenir des UseCases et des ports (interfaces) :\n\n"
                . implode("\n", $violations)
        );
    }

    public function testDiBindingsRespectDddLayers(): void
    {
        $ini      = parse_ini_file(BASE_PATH . '/config/default.ini', true);
        $bindings = $ini['di']['bind'] ?? [];

        $violations = [];

        foreach ($bindings as $port => $adapter) {
            // ── Règles DDD ────────────────────────────────────────────────────

            // Le port doit être une interface hors Infrastructure
            if (str_starts_with($port, 'Rore\\Infrastructure\\')) {
                $violations[] = "Port en Infrastructure : {$port}";
            }

            // L'adaptateur doit être en Infrastructure
            if (!str_starts_with($adapter, 'Rore\\Infrastructure\\')) {
                $violations[] = "Adaptateur hors Infrastructure : {$adapter}\n   → port : {$port}";
            }

            // ── Correctness technique ─────────────────────────────────────────

            // $port doit être une interface
            if (!interface_exists($port)) {
                $violations[] = "N'est pas une interface : {$port}";
                continue; // inutile de tester implements si le port est invalide
            }

            // $adapter doit être une classe existante
            if (!class_exists($adapter)) {
                $violations[] = "Classe introuvable : {$adapter}\n   → port : {$port}";
                continue;
            }

            // $adapter doit implémenter $port
            if (!is_a($adapter, $port, true)) {
                $violations[] = "« {$adapter} » n'implémente pas « {$port} »";
            }
        }

        Assert::equals(
            0,
            count($violations),
            "\n\nLes bindings DI de default.ini violent les règles DDD :\n\n"
                . implode("\n\n", $violations)
        );
    }
}
