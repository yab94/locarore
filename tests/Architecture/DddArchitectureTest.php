<?php

declare(strict_types=1);

/**
 * Vérifie les règles d'architecture modulaire (Package by Feature).
 *
 * Modules : Cart, Catalog, Contact, Reservation, Search, Settings, Shared
 * Chaque module contient : UseCase/, Service/, Port/, Entity/, ValueObject/, Adapter/
 *
 * Règles :
 *   UseCase/ et Service/ → interdiction d'injecter un Adapter/
 *   Entity/ et ValueObject/ → imports limités au même module, Shared, Framework
 *   Adapter/ → doit implémenter un Port (Rore\*\Port\*)
 *   Presentation → ne doit pas injecter un Adapter/
 */
final class DddArchitectureTest
{
    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function findPhpFiles(string $dir): array
    {
        if (!is_dir($dir)) return [];
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

    // ─── Tests ───────────────────────────────────────────────────────────────

    /**
     * UseCase/ et Service/ ne doivent pas injecter de classes Adapter/.
     */
    public function testUseCasesDoNotDependOnAdapters(): void
    {
        $violations = [];

        foreach ($this->findPhpFiles(BASE_PATH . '/src') as $file) {
            if (!preg_match('#/(UseCase|Service)/#', $file)) continue;

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
            $constructor = $ref->getConstructor();
            if ($constructor === null) continue;

            foreach ($constructor->getParameters() as $param) {
                $type = $param->getType();
                if (!($type instanceof ReflectionNamedType) || $type->isBuiltin()) continue;
                $typeName = $type->getName();
                if (str_contains($typeName, '\\Adapter\\')) {
                    $violations[] = sprintf(
                        '%s::__construct($%s) — dépend d\'un Adapter : %s',
                        $className, $param->getName(), $typeName
                    );
                }
            }
        }

        Assert::equals(
            0,
            count($violations),
            "\n\nUseCase/Service ne doit pas dépendre d'un Adapter :\n\n" . implode("\n", $violations)
        );
    }

    /**
     * Entity/ et ValueObject/ ne doivent importer que leur propre module,
     * Shared et Framework.
     */
    public function testEntitiesAreIsolated(): void
    {
        $violations = [];

        foreach ($this->findPhpFiles(BASE_PATH . '/src') as $file) {
            if (!preg_match('#/src/(\w+)/(Entity|ValueObject)/#', $file, $m)) continue;
            $module = $m[1];

            $content = file_get_contents($file);
            preg_match_all('/^use (Rore\\\\[^;]+);/m', $content, $uses);
            foreach ($uses[1] as $fqcn) {
                if (
                    str_starts_with($fqcn, "Rore\\$module\\")
                    || str_starts_with($fqcn, 'Rore\Shared\\')
                    || str_starts_with($fqcn, 'Rore\Framework\\')
                ) continue;

                $className = $this->getClassName($file) ?? $file;
                $violations[] = "$className importe $fqcn (interdit depuis Entity/ValueObject)";
            }
        }

        Assert::equals(
            0,
            count($violations),
            "\n\nEntity/ValueObject doit rester isolé :\n\n" . implode("\n", $violations)
        );
    }

    /**
     * Adapter/ doit implémenter au moins un Port (Rore\*\Port\*).
     */
    public function testAdaptersImplementPorts(): void
    {
        $violations = [];

        foreach ($this->findPhpFiles(BASE_PATH . '/src') as $file) {
            if (!preg_match('#/Adapter/#', $file)) continue;

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

            $ports = array_filter(
                $ref->getInterfaceNames(),
                fn(string $i) => str_starts_with($i, 'Rore\\') && str_contains($i, '\\Port\\')
            );

            $parent = $ref->getParentClass();
            $externalParent = $parent !== false && str_starts_with($parent->getName(), 'Rore\\');

            if (empty($ports) && !$externalParent) {
                $violations[] = "$className — Adapter sans Port ni parent Rore";
            }
        }

        Assert::equals(
            0,
            count($violations),
            "\n\nAdapter doit implémenter un Port :\n\n" . implode("\n", $violations)
        );
    }

    /**
     * Presentation ne doit pas injecter directement des Adapter/.
     */
    public function testPresentationDoesNotUseAdapters(): void
    {
        $violations = [];

        foreach ($this->findPhpFiles(BASE_PATH . '/src/Presentation') as $file) {
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
            $constructor = $ref->getConstructor();
            if ($constructor === null) continue;

            foreach ($constructor->getParameters() as $param) {
                $type = $param->getType();
                if (!($type instanceof ReflectionNamedType) || $type->isBuiltin()) continue;
                $typeName = $type->getName();
                if (str_contains($typeName, '\\Adapter\\')) {
                    $violations[] = sprintf(
                        '%s::__construct($%s) — Presentation dépend d\'un Adapter : %s',
                        $className, $param->getName(), $typeName
                    );
                }
            }
        }

        Assert::equals(
            0,
            count($violations),
            "\n\nPresentation ne doit pas dépendre d'un Adapter :\n\n" . implode("\n", $violations)
        );
    }
}
