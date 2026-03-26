<?php

declare(strict_types=1);

namespace Rore\Framework\View;

use InvalidArgumentException;

/**
 * Moteur de template PHP minimaliste avec scope déclaratif.
 *
 * Deux variables sont automatiquement disponibles dans chaque template :
 *   - $tpl     → cette instance (accès typé aux params via get/tryGet)
 *   - $partial → callable : fn(string $file, array $extra = []): string
 *
 * Usage dans un template :
 *   $html = HtmlHelper::cast($tpl->get('html'));
 *   $items = Cast::array($tpl->tryGet('items', []));
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

    // ─── Accès aux params ────────────────────────────────────────────────────

    public function get(string $key): mixed
    {
        return $this->tryGet($key) ?? throw new InvalidArgumentException(
            sprintf('Template "%s" : param "$%s" absent.', $this->file, $key)
        );
    }

    public function tryGet(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->params) ? $this->params[$key] : $default;
    }
}
