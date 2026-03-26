<?php

declare(strict_types=1);

namespace Rore\Framework;

use ReflectionClass;
use ReflectionMethod;

/**
 * Scanne récursivement un répertoire de controllers et collecte
 * toutes les méthodes annotées avec #[Route].
 *
 * Retourne un tableau de routes :
 *   [['method' => 'GET', 'path' => '/panier', 'handler' => 'FQCN.methodName'], ...]
 */
final class RouteScanner
{
    /**
     * @param string $baseDir       Chemin absolu du répertoire racine des controllers
     * @param string $baseNamespace Namespace PHP correspondant (ex: "Rore\Presentation\Controller")
     */
    public function __construct(
        private readonly string $baseDir,
        private readonly string $baseNamespace,
    ) {}

    /**
     * @return array<array{method: string, path: string, handler: string}>
     */
    public function scan(): array
    {
        $routes = [];

        foreach ($this->findPhpFiles($this->baseDir) as $file) {
            $fqcn = $this->fileToFqcn($file);
            if ($fqcn === null) {
                continue;
            }

            $ref = new ReflectionClass($fqcn);
            if (!$ref->isInstantiable()) {
                continue;
            }

            foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->getDeclaringClass()->getName() !== $fqcn) {
                    continue; // ignorer les méthodes héritées
                }

                $attrs = $method->getAttributes(Route::class);
                foreach ($attrs as $attr) {
                    /** @var Route $route */
                    $route    = $attr->newInstance();
                    $routes[] = [
                        'method'  => $route->method,
                        'path'    => $route->path,
                        'handler' => $fqcn . '.' . $method->getName(),
                    ];
                }
            }
        }

        return $routes;
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

    private function fileToFqcn(string $file): ?string
    {
        // Chemin relatif depuis baseDir → segments namespace
        $relative = ltrim(str_replace($this->baseDir, '', $file), DIRECTORY_SEPARATOR);
        $relative = str_replace(DIRECTORY_SEPARATOR, '\\', $relative);
        $relative = preg_replace('/\.php$/', '', $relative);

        return $this->baseNamespace . '\\' . $relative;
    }
}
