<?php

declare(strict_types=1);

/**
 * Vérifie les règles de dépendances inter-couches DDD.
 *
 * Règles :
 *   Presentation\Controller  →  Application, Domain, Presentation, Framework
 *   Application              →  Application, Domain, Framework
 *   Domain                   →  Domain, Framework
 *   Infrastructure           →  Domain, Infrastructure, Application, Framework
 *                               (les adaptateurs Infrastructure implémentent des ports Application)
 *
 * Framework : Couche framework de base accessible par toutes les couches
 *             (Config, Container, Cast, Typable, etc.)
 *
 * Contrainte supplémentaire :
 *   Application ne contient QUE des UseCases (*UseCase) et des ports (interfaces).
 */
final class DddArchitectureTest
{
    // ─── Règles ──────────────────────────────────────────────────────────────
    // Ordre : du plus spécifique au plus général (premier match gagne).
    private const RULES = [
        'Rore\Presentation\Controller' => ['Rore\Application', 'Rore\Domain', 'Rore\Presentation', 'Rore\Framework'],
        'Rore\Application'             => ['Rore\Application', 'Rore\Domain', 'Rore\Framework'],
        'Rore\Domain'                  => ['Rore\Domain', 'Rore\Framework'],
        'Rore\Infrastructure'          => ['Rore\Domain', 'Rore\Infrastructure', 'Rore\Application', 'Rore\Framework'],
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

            // Les classes concrètes doivent se terminer par UseCase ou Service
            $shortName = $ref->getShortName();
            if (!str_ends_with($shortName, 'UseCase') && !str_ends_with($shortName, 'Service')) {
                $violations[] = sprintf(
                    '%s — classe concrète non-UseCase/Service en Application (renommer en *UseCase, *Service ou déplacer en Infrastructure)',
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

    public function testDomainOnlyAllowedSubdirectories(): void
    {
        // Sous-dossiers autorisés dans Domain (par module)
        $allowedDirs = ['Entity', 'ValueObject', 'Repository', 'Service'];

        $violations = [];

        foreach ($this->findPhpFiles(BASE_PATH . '/src/Domain') as $file) {
            $className = $this->getClassName($file);
            if ($className === null) continue;

            // Extraire le sous-dossier immédiatement sous Domain/{Module}/
            // Exemple : src/Domain/Catalog/Entity/Product.php → "Entity"
            $relative = str_replace(BASE_PATH . '/src/Domain/', '', $file);
            $parts    = explode('/', $relative);

            // parts[0] = module (Catalog, Cart…), parts[1] = sous-dossier, parts[2] = fichier
            if (count($parts) < 3) {
                $violations[] = sprintf(
                    '%s — fichier à la racine d\'un module Domain (doit être dans Entity/, ValueObject/, Repository/ ou Service/)',
                    $className,
                );
                continue;
            }

            $subDir = $parts[1];
            if (!in_array($subDir, $allowedDirs, true)) {
                $violations[] = sprintf(
                    '%s — sous-dossier Domain non autorisé : "%s" (autorisés : %s)',
                    $className,
                    $subDir,
                    implode(', ', $allowedDirs),
                );
            }
        }

        Assert::equals(
            0,
            count($violations),
            "\n\nDomain doit uniquement contenir Entity/, ValueObject/, Repository/, Service/ :\n\n"
                . implode("\n", $violations)
        );
    }

    public function testDomainServicesArePure(): void
    {
        $violations = [];

        foreach ($this->findPhpFiles(BASE_PATH . '/src/Domain') as $file) {
            // Seuls les fichiers dans un sous-dossier Service/ sont concernés
            if (!str_contains($file, '/Domain/') || !str_contains($file, '/Service/')) continue;

            $className = $this->getClassName($file);
            if ($className === null) continue;

            try {
                require_once $file;
                if (!class_exists($className)) continue;
                $ref = new ReflectionClass($className);
            } catch (Throwable) {
                continue;
            }

            if ($ref->isInterface() || $ref->isAbstract()) continue;

            // Vérifier les interfaces implémentées
            foreach ($ref->getInterfaceNames() as $iface) {
                if (str_starts_with($iface, 'Rore\\') && !str_starts_with($iface, 'Rore\Domain\\')) {
                    $violations[] = sprintf(
                        "%s implements %s\n   → les classes Domain ne peuvent implémenter que des interfaces Rore\\Domain\\*",
                        $className,
                        $iface,
                    );
                }
            }

            $constructor = $ref->getConstructor();
            if ($constructor === null) continue;

            foreach ($constructor->getParameters() as $param) {
                if ($param->isVariadic()) continue;

                $type = $param->getType();
                if ($type === null || !($type instanceof ReflectionNamedType)) continue;
                if ($type->isBuiltin()) continue;

                $typeName = $type->getName();

                // Un service Domain ne peut injecter que des types Rore\Domain\*
                if (!str_starts_with($typeName, 'Rore\Domain\\')) {
                    $violations[] = sprintf(
                        "%s::__construct() — \$%s : %s\n   → les services Domain ne peuvent injecter que des types Rore\\Domain\\*",
                        $className,
                        $param->getName(),
                        $typeName,
                    );
                }
            }
        }

        Assert::equals(
            0,
            count($violations),
            "\n\nLes services Domain doivent être purs (pas d'injection hors Domain) :\n\n"
                . implode("\n\n", $violations)
        );
    }

    public function testInfrastructureClassesAreAdapters(): void
    {
        $violations = [];

        foreach ($this->findPhpFiles(BASE_PATH . '/src/Infrastructure') as $file) {
            $className = $this->getClassName($file);
            if ($className === null) continue;

            try {
                require_once $file;
                if (!class_exists($className)) continue;
                $ref = new ReflectionClass($className);
            } catch (Throwable) {
                continue;
            }

            if ($ref->isAbstract() || $ref->isInterface()) continue;

            // Chaque classe concrète Infrastructure doit implémenter au moins un port
            // défini hors Infrastructure (Domain, Application ou Framework),
            // OU étendre une classe parente hors Infrastructure.
            $externalInterfaces = array_filter(
                $ref->getInterfaceNames(),
                fn(string $iface) => str_starts_with($iface, 'Rore\\')
                    && !str_starts_with($iface, 'Rore\\Infrastructure\\'),
            );

            $parent = $ref->getParentClass();
            $externalParent = $parent !== false
                && (
                    str_starts_with($parent->getName(), 'RRB\\')
                    || (str_starts_with($parent->getName(), 'Rore\\')
                        && !str_starts_with($parent->getName(), 'Rore\\Infrastructure\\'))
                );

            if (empty($externalInterfaces) && !$externalParent) {
                $violations[] = sprintf(
                    '%s — classe Infrastructure sans port externe (Domain/Application/Framework)'
                        . "\n   → n'implémente aucune interface hors Infrastructure et n'étend aucune classe hors Infrastructure",
                    $className,
                );
            }
        }

        Assert::equals(
            0,
            count($violations),
            "\n\nInfrastructure doit uniquement contenir des adaptateurs (classes implémentant un port externe) :\n\n"
                . implode("\n\n", $violations)
        );
    }

    public function testDiBindingsRespectDddLayers(): void
    {
        $ini      = parse_ini_file(BASE_PATH . '/config/default.ini', true);
        $bindings = $ini['di']['bind'] ?? [];

        $violations = [];

        foreach ($bindings as $port => $adapter) {
            // ── Règles DDD ────────────────────────────────────────────────────

            // Binding Framework→Framework : implémentations internes au framework,
            // exemptées des règles DDD (pas de port applicatif, pas d'adaptateur externe)
            $isFrameworkBinding = str_starts_with($port, 'Rore\\Framework\\')
                               && str_starts_with($adapter, 'Rore\\Framework\\');

            if (!$isFrameworkBinding) {
                // Le port doit être une interface hors Infrastructure
                if (str_starts_with($port, 'Rore\\Infrastructure\\')) {
                    $violations[] = "Port en Infrastructure : {$port}";
                }

                // L'adaptateur doit être en Infrastructure
                if (!str_starts_with($adapter, 'Rore\\Infrastructure\\')) {
                    $violations[] = "Adaptateur hors Infrastructure : {$adapter}\n   → port : {$port}";
                }
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

    public function testUseCaseConstructorsOnlyAcceptInterfaces(): void
    {
        $violations = [];

        foreach ($this->findPhpFiles(BASE_PATH . '/src/Application') as $file) {
            $className = $this->getClassName($file);
            if ($className === null) continue;
            if (!str_ends_with($className, 'UseCase')) continue;

            try {
                require_once $file;
                if (!class_exists($className)) continue;
                $ref = new ReflectionClass($className);
            } catch (Throwable) {
                continue;
            }

            $constructor = $ref->getConstructor();
            if ($constructor === null) continue;

            foreach ($constructor->getParameters() as $param) {
                if ($param->isVariadic()) continue;

                $type = $param->getType();
                if ($type === null || !($type instanceof ReflectionNamedType)) continue;
                if ($type->isBuiltin()) continue; // scalaires OK (#[BindConfig])

                $typeName = $type->getName();

                // Interfaces → OK
                if (interface_exists($typeName)) continue;

                // UseCase → UseCase : orchestration autorisée
                if (str_ends_with($typeName, 'UseCase')) continue;

                $violations[] = sprintf(
                    "%s::__construct() — \$%s : %s\n   → les UseCases ne doivent injecter que des interfaces (ou d'autres *UseCase)",
                    $className,
                    $param->getName(),
                    $typeName,
                );
            }
        }

        Assert::equals(
            0,
            count($violations),
            "\n\nUseCases avec injection de classe concrète :\n\n" . implode("\n\n", $violations)
        );
    }
}
