<?php

declare(strict_types=1);

use Rore\Framework\Support\Typable;

class TypableTest
{
    // ─── get() ───────────────────────────────────────────────────────────────

    public function testGetReturnsValue(): void
    {
        $t = new Typable(['foo' => 'bar']);
        Assert::equals('bar', $t->get('foo'));
    }

    public function testGetWithDefaultReturnsDefaultWhenMissing(): void
    {
        $t = new Typable([]);
        Assert::equals('fallback', $t->get('missing', 'fallback'));
    }

    public function testGetWithNullDefaultReturnsNull(): void
    {
        $t = new Typable([]);
        Assert::null($t->get('missing', null));
    }

    public function testGetThrowsWhenMissingAndNoDefault(): void
    {
        $t = new Typable([]);
        Assert::throws(\InvalidArgumentException::class, fn() => $t->get('missing'));
    }

    public function testGetReturnsNullValueWhenKeyExists(): void
    {
        $t = new Typable(['key' => null]);
        Assert::null($t->get('key'));
    }

    // ─── getString() ─────────────────────────────────────────────────────────

    public function testGetStringReturnsString(): void
    {
        $t = new Typable(['name' => 'hello']);
        Assert::equals('hello', $t->getString('name'));
    }

    public function testGetStringThrowsWhenMissing(): void
    {
        $t = new Typable([]);
        Assert::throws(\InvalidArgumentException::class, fn() => $t->getString('name'));
    }

    public function testGetStringReturnsDefaultWhenMissing(): void
    {
        $t = new Typable([]);
        Assert::equals('', $t->getString('name', ''));
    }

    public function testGetStringThrowsWhenNotString(): void
    {
        $t = new Typable(['count' => 42]);
        Assert::throws(\InvalidArgumentException::class, fn() => $t->getString('count'));
    }

    // ─── getInt() ────────────────────────────────────────────────────────────

    public function testGetIntReturnsInt(): void
    {
        $t = new Typable(['qty' => '5']);
        Assert::equals(5, $t->getInt('qty'));
    }

    public function testGetIntThrowsWhenMissing(): void
    {
        $t = new Typable([]);
        Assert::throws(\InvalidArgumentException::class, fn() => $t->getInt('qty'));
    }

    public function testGetIntReturnsDefaultWhenMissing(): void
    {
        $t = new Typable([]);
        Assert::equals(0, $t->getInt('qty', 0));
    }

    public function testGetIntThrowsWhenNotNumeric(): void
    {
        $t = new Typable(['qty' => 'abc']);
        Assert::throws(\InvalidArgumentException::class, fn() => $t->getInt('qty'));
    }

    public function testGetIntCastsNumericString(): void
    {
        $t = new Typable(['qty' => '42']);
        Assert::equals(42, $t->getInt('qty'));
    }

    // ─── getFloat() ──────────────────────────────────────────────────────────

    public function testGetFloatReturnsFloat(): void
    {
        $t = new Typable(['price' => '3.14']);
        Assert::equals(3.14, $t->getFloat('price'));
    }

    public function testGetFloatThrowsWhenMissing(): void
    {
        $t = new Typable([]);
        Assert::throws(\InvalidArgumentException::class, fn() => $t->getFloat('price'));
    }

    public function testGetFloatReturnsDefaultWhenMissing(): void
    {
        $t = new Typable([]);
        Assert::equals(0.0, $t->getFloat('price', 0.0));
    }

    public function testGetFloatThrowsWhenNotNumeric(): void
    {
        $t = new Typable(['price' => 'free']);
        Assert::throws(\InvalidArgumentException::class, fn() => $t->getFloat('price'));
    }

    // ─── getArray() ──────────────────────────────────────────────────────────

    public function testGetArrayReturnsArray(): void
    {
        $t = new Typable(['items' => [1, 2, 3]]);
        Assert::equals([1, 2, 3], $t->getArray('items'));
    }

    public function testGetArrayThrowsWhenMissing(): void
    {
        $t = new Typable([]);
        Assert::throws(\InvalidArgumentException::class, fn() => $t->getArray('items'));
    }

    public function testGetArrayReturnsDefaultWhenMissing(): void
    {
        $t = new Typable([]);
        Assert::equals([], $t->getArray('items', []));
    }

    public function testGetArrayThrowsWhenNotArray(): void
    {
        $t = new Typable(['items' => 'not-an-array']);
        Assert::throws(\InvalidArgumentException::class, fn() => $t->getArray('items'));
    }
}
