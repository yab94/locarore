<?php

declare(strict_types=1);

namespace RRB\Http;

use ReflectionClass;
use ReflectionMethod;

/**
 * Scanne récursivement un ou plusieurs répertoires de controllers et collecte
 * toutes les méthodes annotées avec #[Route].
 *
 * Usage :
 *   $scanner->scan($dir1, $ns1);
 *   $scanner->scan($dir2, $ns2);
 *   $routes = $scanner->getRoutes();
 */
final class RouteScanner
{
    /** @var array<array{method: string, path: string, handler: string}> */
    private array $routes = [];

    /**
     * Scanne $baseDir et accumule les routes trouvées.
     *
     * @param string $baseDir       Chemin absolu du répertoire racine des controllers
     * @param string $baseNamespace Namespace PHP correspondant (ex: "Rore\Presentation\Controller")
     */
    public function scan(string $baseDir, string $baseNamespace): void
    {
        foreach ($this->findPhpFiles($baseDir) as $file) {
            $fqcn = $this->fileToFqcn($file, $baseDir, $baseNamespace);
            if ($fqcn === null) {
                continue;
            }

            $this->scanController($fqcn);
        }
    }

    public function scanController(string $fqcn): void
    {
        $ref = new ReflectionClass($fqcn);
        if (!$ref->isInstantiable()) {
            return;
        }

        foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getDeclaringClass()->getName() !== $fqcn) {
                continue; // ignorer les méthodes héritées
            }

            $attrs = $method->getAttributes(Route::class);
            foreach ($attrs as $attr) {
                /** @var Route $route */
                $route          = $attr->newInstance();
                $this->routes[] = [
                    'method'  => $route->method,
                    'path'    => $route->path,
                    'handler' => $fqcn . '.' . $method->getName(),
                ];
            }
        }
    }

    /**
     * Retourne toutes les routes accumulées par les appels à scan().
     *
     * @return array<array{method: string, path: string, handler: string}>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /** @return \Generator<string> */
    private function findPhpFiles(string $dir): \Generator
    {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                yield $file->getPathname();
            }
        }
    }

    private function fileToFqcn(string $file, string $baseDir, string $baseNamespace): ?string
    {
        // Chemin relatif depuis baseDir → segments namespace
        $relative = ltrim(str_replace($baseDir, '', $file), DIRECTORY_SEPARATOR);
        $relative = str_replace(DIRECTORY_SEPARATOR, '\\', $relative);
        $relative = preg_replace('/\.php$/', '', $relative);

        return $baseNamespace . '\\' . $relative;
    }
}
