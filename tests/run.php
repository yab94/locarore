<?php

declare(strict_types=1);

/**
 * Micro test-runner — zéro dépendance.
 *
 * Usage :
 *   php tests/run.php
 *   php tests/run.php tests/Domain/Catalog/SlugTest.php   (fichier unique)
 */

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class): void {
    $prefix = 'Rore\\';
    if (!str_starts_with($class, $prefix)) return;
    $file = BASE_PATH . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (file_exists($file)) require_once $file;
});

// ─── Assertion helpers ──────────────────────────────────────────────────────

final class Assert
{
    public static function equals(mixed $expected, mixed $actual, string $msg = ''): void
    {
        if ($expected !== $actual) {
            self::fail(
                sprintf("Expected %s, got %s%s",
                    self::repr($expected),
                    self::repr($actual),
                    $msg !== '' ? " — $msg" : ''
                )
            );
        }
    }

    public static function true(bool $value, string $msg = ''): void
    {
        if (!$value) {
            self::fail("Expected true, got false" . ($msg !== '' ? " — $msg" : ''));
        }
    }

    public static function false(bool $value, string $msg = ''): void
    {
        if ($value) {
            self::fail("Expected false, got true" . ($msg !== '' ? " — $msg" : ''));
        }
    }

    public static function null(mixed $value, string $msg = ''): void
    {
        if ($value !== null) {
            self::fail("Expected null, got " . self::repr($value) . ($msg !== '' ? " — $msg" : ''));
        }
    }

    public static function notNull(mixed $value, string $msg = ''): void
    {
        if ($value === null) {
            self::fail("Expected non-null value" . ($msg !== '' ? " — $msg" : ''));
        }
    }

    public static function throws(string $exceptionClass, callable $fn, string $msg = ''): void
    {
        try {
            $fn();
            self::fail("Expected $exceptionClass to be thrown" . ($msg !== '' ? " — $msg" : ''));
        } catch (\Throwable $e) {
            if (!($e instanceof $exceptionClass)) {
                self::fail(
                    sprintf("Expected %s, got %s(%s)%s",
                        $exceptionClass, get_class($e), $e->getMessage(),
                        $msg !== '' ? " — $msg" : ''
                    )
                );
            }
        }
    }

    private static function fail(string $message): never
    {
        throw new \RuntimeException($message);
    }

    private static function repr(mixed $v): string
    {
        return match(true) {
            is_string($v) => '"' . $v . '"',
            is_bool($v)   => $v ? 'true' : 'false',
            is_null($v)   => 'null',
            default       => (string) $v,
        };
    }
}

// ─── Runner ─────────────────────────────────────────────────────────────────

final class TestRunner
{
    private int $passed  = 0;
    private int $failed  = 0;
    /** @var array<string> */
    private array $failures = [];

    /**
     * Exécute tous les tests d'une classe de test.
     * Les méthodes commençant par "test" sont lancées.
     */
    public function run(string $class): void
    {
        $methods = array_filter(
            get_class_methods($class),
            fn(string $m) => str_starts_with($m, 'test')
        );
        $obj = new $class();
        foreach ($methods as $method) {
            try {
                // setUp optionnel
                if (method_exists($obj, 'setUp')) {
                    $obj->setUp();
                }
                $obj->$method();
                $this->passed++;
                echo "\033[32m.\033[0m";
            } catch (\Throwable $e) {
                $this->failed++;
                $label = $class . '::' . $method;
                $this->failures[] = "$label\n   " . $e->getMessage()
                    . "\n   " . $e->getFile() . ':' . $e->getLine();
                echo "\033[31mF\033[0m";
            }
        }
    }

    public function report(): void
    {
        echo "\n\n";
        if (!empty($this->failures)) {
            echo "\033[31mÉCHECS :\033[0m\n";
            foreach ($this->failures as $i => $f) {
                echo sprintf("  %d) %s\n\n", $i + 1, $f);
            }
        }
        $total = $this->passed + $this->failed;
        $color = $this->failed > 0 ? "\033[31m" : "\033[32m";
        echo sprintf(
            "%s%d test(s), %d succès, %d échec(s)\033[0m\n",
            $color, $total, $this->passed, $this->failed
        );
    }

    public function hasFailed(): bool
    {
        return $this->failed > 0;
    }
}

// ─── Découverte & exécution ─────────────────────────────────────────────────

$runner = new TestRunner();

/**
 * Récursive : trouve tous les *Test.php sous $dir.
 * @return array<string>
 */
function findTestFiles(string $dir): array
{
    $files = [];
    foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), 'Test.php')) {
            $files[] = $file->getPathname();
        }
    }
    sort($files);
    return $files;
}

// Ciblage d'un fichier unique via argument CLI, sinon tous les *Test.php
$targets = isset($argv[1])
    ? [$argv[1]]
    : findTestFiles(__DIR__);

foreach ($targets as $file) {
    $before = get_declared_classes();
    require_once $file;
    $after  = get_declared_classes();
    $newClasses = array_diff($after, $before);
    foreach ($newClasses as $class) {
        if (str_ends_with($class, 'Test')) {
            $runner->run($class);
        }
    }
}

$runner->report();
exit($runner->hasFailed() ? 1 : 0);
