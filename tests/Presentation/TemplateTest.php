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

    // ─── assertString ────────────────────────────────────────────────────────

    public function testAssertStringPassesOnString(): void
    {
        $this->make('<?php $tpl->assertString("title"); ?>', ['title' => 'Hello'])->render();
        Assert::true(true);
    }

    public function testAssertStringThrowsOnMissingKey(): void
    {
        Assert::throws(\InvalidArgumentException::class,
            fn() => $this->make('<?php $tpl->assertString("title"); ?>')->render()
        );
    }

    public function testAssertStringThrowsOnWrongType(): void
    {
        Assert::throws(\InvalidArgumentException::class,
            fn() => $this->make('<?php $tpl->assertString("title"); ?>', ['title' => 42])->render()
        );
    }

    public function testAssertStringThrowsOnNullWhenNotNullable(): void
    {
        Assert::throws(\InvalidArgumentException::class,
            fn() => $this->make('<?php $tpl->assertString("title"); ?>', ['title' => null])->render()
        );
    }

    public function testAssertStringPassesOnNullWhenNullable(): void
    {
        $this->make('<?php $tpl->assertString("title", nullable: true); ?>', ['title' => null])->render();
        Assert::true(true);
    }

    // ─── assertInt ───────────────────────────────────────────────────────────

    public function testAssertIntPassesOnInt(): void
    {
        $this->make('<?php $tpl->assertInt("count"); ?>', ['count' => 3])->render();
        Assert::true(true);
    }

    public function testAssertIntThrowsOnString(): void
    {
        Assert::throws(\InvalidArgumentException::class,
            fn() => $this->make('<?php $tpl->assertInt("count"); ?>', ['count' => '3'])->render()
        );
    }

    public function testAssertIntNullable(): void
    {
        $this->make('<?php $tpl->assertInt("count", nullable: true); ?>', ['count' => null])->render();
        Assert::true(true);
    }

    // ─── assertFloat ─────────────────────────────────────────────────────────

    public function testAssertFloatPassesOnFloat(): void
    {
        $this->make('<?php $tpl->assertFloat("price"); ?>', ['price' => 9.99])->render();
        Assert::true(true);
    }

    public function testAssertFloatPassesOnInt(): void
    {
        $this->make('<?php $tpl->assertFloat("price"); ?>', ['price' => 10])->render();
        Assert::true(true);
    }

    public function testAssertFloatThrowsOnString(): void
    {
        Assert::throws(\InvalidArgumentException::class,
            fn() => $this->make('<?php $tpl->assertFloat("price"); ?>', ['price' => '9.99'])->render()
        );
    }

    // ─── assertBool ──────────────────────────────────────────────────────────

    public function testAssertBoolPassesOnBool(): void
    {
        $this->make('<?php $tpl->assertBool("active"); ?>', ['active' => false])->render();
        Assert::true(true);
    }

    public function testAssertBoolThrowsOnInt(): void
    {
        Assert::throws(\InvalidArgumentException::class,
            fn() => $this->make('<?php $tpl->assertBool("active"); ?>', ['active' => 0])->render()
        );
    }

    // ─── assertArray ─────────────────────────────────────────────────────────

    public function testAssertArrayPassesOnArray(): void
    {
        $this->make('<?php $tpl->assertArray("items"); ?>', ['items' => [1, 2, 3]])->render();
        Assert::true(true);
    }

    public function testAssertArrayThrowsOnObject(): void
    {
        Assert::throws(\InvalidArgumentException::class,
            fn() => $this->make('<?php $tpl->assertArray("items"); ?>', ['items' => new \stdClass()])->render()
        );
    }

    // ─── assertInstanceOf ────────────────────────────────────────────────────

    public function testAssertInstanceOfPasses(): void
    {
        $this->make('<?php $tpl->assertInstanceOf("obj", \stdClass::class); ?>', ['obj' => new \stdClass()])->render();
        Assert::true(true);
    }

    public function testAssertInstanceOfThrowsOnWrongClass(): void
    {
        Assert::throws(\InvalidArgumentException::class,
            fn() => $this->make('<?php $tpl->assertInstanceOf("obj", \ArrayObject::class); ?>', ['obj' => new \stdClass()])->render()
        );
    }

    public function testAssertInstanceOfNullable(): void
    {
        $this->make('<?php $tpl->assertInstanceOf("obj", \stdClass::class, nullable: true); ?>', ['obj' => null])->render();
        Assert::true(true);
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
