<?php

declare(strict_types=1);

namespace Rore\Presentation\Template;

use InvalidArgumentException;

/**
 * Moteur de template PHP minimaliste avec scope déclaratif.
 *
 * Trois variables sont automatiquement disponibles dans chaque template :
 *   - $tpl     → cette instance (assertions, accès aux params)
 *   - $partial → callable : fn(string $file, array $extra = []): string
 *
 * Usage dans un template :
 *   <?php $tpl->assertString('title'); $tpl->assertArray('items'); ?>
 *   <?= $partial('partials/card', ['item' => $item]) ?>
 */
final class Template
{
    public function __construct(
        private readonly string $file,
        private readonly array  $params = []
    ) {}

    // ─── Rendu ───────────────────────────────────────────────────────────────

    public function render(): string
    {
        // $tpl et $partial sont définis avant extract → EXTR_SKIP les protège
        $tpl     = $this;
        $partial = fn(string $file, array $extra = []): string => $this->partial($file, $extra);

        // Copie locale nécessaire : extract() ne peut pas modifier un readonly array
        $vars = $this->params;
        extract($vars, EXTR_SKIP);

        ob_start();
        require $this->file . '.php';
        return (string) ob_get_clean();
    }

    /**
     * Crée un sous-template en héritant des params courants.
     * Les $extra ont priorité sur les params hérités.
     */
    public function partial(string $file, array $extra = []): string
    {
        return (new self($file, [...$this->params, ...$extra]))->render();
    }

    // ─── Assertions (appelables via $tpl dans les templates) ─────────────────

    private function assertString(string $key, bool $nullable = false): void
    {
        $this->assertKey($key, $nullable);
        if ($this->params[$key] !== null && !is_string($this->params[$key])) {
            $this->fail($key, 'string', $this->params[$key]);
        }
    }

    private function assertInt(string $key, bool $nullable = false): void
    {
        $this->assertKey($key, $nullable);
        if ($this->params[$key] !== null && !is_int($this->params[$key])) {
            $this->fail($key, 'int', $this->params[$key]);
        }
    }

    private function assertFloat(string $key, bool $nullable = false): void
    {
        $this->assertKey($key, $nullable);
        if ($this->params[$key] !== null && !is_float($this->params[$key]) && !is_int($this->params[$key])) {
            $this->fail($key, 'float', $this->params[$key]);
        }
    }

    private function assertBool(string $key, bool $nullable = false): void
    {
        $this->assertKey($key, $nullable);
        if ($this->params[$key] !== null && !is_bool($this->params[$key])) {
            $this->fail($key, 'bool', $this->params[$key]);
        }
    }

    private function assertArray(string $key, bool $nullable = false): void
    {
        $this->assertKey($key, $nullable);
        if ($this->params[$key] !== null && !is_array($this->params[$key])) {
            $this->fail($key, 'array', $this->params[$key]);
        }
    }

    private function assertInstanceOf(string $key, string $class, bool $nullable = false): void
    {
        $this->assertKey($key, $nullable);
        if ($this->params[$key] !== null && !($this->params[$key] instanceof $class)) {
            $this->fail($key, $class, $this->params[$key]);
        }
    }

    // ─── Internals ───────────────────────────────────────────────────────────

    private function assertKey(string $key, bool $nullable): void
    {
        if (!array_key_exists($key, $this->params)) {
            throw new InvalidArgumentException(
                sprintf('Template "%s" : param "$%s" absent.', $this->file, $key)
            );
        }

        if (!$nullable && $this->params[$key] === null) {
            throw new InvalidArgumentException(
                sprintf('Template "%s" : param "$%s" est null (non nullable).', $this->file, $key)
            );
        }
    }

    private function fail(string $key, string $expected, mixed $actual): never
    {
        throw new InvalidArgumentException(
            sprintf(
                'Template "%s" : param "$%s" doit être %s, %s fourni.',
                $this->file,
                $key,
                $expected,
                get_debug_type($actual)
            )
        );
    }
}
