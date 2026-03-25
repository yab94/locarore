<?php

declare(strict_types=1);

use Rore\Presentation\Template\Template;

/**
 * Tests unitaires de Template.
 *
 * Pas de require de fichier PHP réel : on utilise des fichiers temporaires
 * créés dans sys_get_temp_dir() pour chaque cas de test.
 */
final class TemplateTest
{
    // ─── render ──────────────────────────────────────────────────────────────

    public function testRenderExtractsParams(): void
    {
        $tpl  = $this->make('<?= $name ?>', ['name' => 'Locarore']);
        Assert::equals('Locarore', $tpl->render());
    }

    public function testRenderExposesTplVariable(): void
    {
        $tpl = $this->make('<?= ($tpl instanceof \Rore\Presentation\Template\Template) ? "ok" : "ko" ?>');
        Assert::equals('ok', $tpl->render());
    }

    public function testRenderExposesPartialCallable(): void
    {
        $tpl = $this->make('<?= is_callable($partial) ? "ok" : "ko" ?>');
        Assert::equals('ok', $tpl->render());
    }

    public function testParamCannotOverrideTpl(): void
    {
        // EXTR_SKIP doit protéger $tpl
        $tpl = $this->make(
            '<?= ($tpl instanceof \Rore\Presentation\Template\Template) ? "ok" : "ko" ?>',
            ['tpl' => 'overridden']
        );
        Assert::equals('ok', $tpl->render());
    }

    // ─── partial ─────────────────────────────────────────────────────────────

    public function testPartialInheritsParentParams(): void
    {
        $child  = $this->makeFile('<?= $inherited ?>');
        $parent = $this->makeFile('<?= $partial("' . $this->stripExt($child) . '") ?>');

        $tpl = new Template($this->stripExt($parent), ['inherited' => 'hello']);
        Assert::equals('hello', $tpl->render());
    }

    public function testPartialExtraOverridesParentParam(): void
    {
        $child  = $this->makeFile('<?= $val ?>');
        $parent = $this->makeFile('<?= $partial("' . $this->stripExt($child) . '", ["val" => "child"]) ?>');

        $tpl = new Template($this->stripExt($parent), ['val' => 'parent']);
        Assert::equals('child', $tpl->render());
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /** Crée un template depuis du contenu inline (fichier tmp sans extension .php) */
    private function make(string $content, array $params = []): Template
    {
        $path = $this->makeFile($content);
        return new Template($this->stripExt($path), $params);
    }

    /** Écrit un fichier .php temporaire et retourne son chemin absolu avec extension */
    private function makeFile(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'tpl_') . '.php';
        file_put_contents($path, $content);
        return $path;
    }

    /** Retire l'extension .php pour correspondre à ce que Template attend */
    private function stripExt(string $path): string
    {
        return substr($path, 0, -4); // retire '.php'
    }
}
