<?php

declare(strict_types=1);

use Rore\ValueObject\Slug;

class SlugTest
{
    public function testBasicAscii(): void
    {
        Assert::equals('vase-en-verre', Slug::from('Vase en verre')->getValue());
    }

    public function testAccents(): void
    {
        Assert::equals('decoration-ete', Slug::from('Décoration Été')->getValue());
    }

    public function testMultipleSpaces(): void
    {
        Assert::equals('art-de-la-table', Slug::from('Art   de  la   table')->getValue());
    }

    public function testLeadingTrailingSpaces(): void
    {
        Assert::equals('mariage', Slug::from('  Mariage  ')->getValue());
    }

    public function testSpecialCharsStripped(): void
    {
        Assert::equals('bougie-parfumee', Slug::from('Bougie & Parfumée !')->getValue());
    }

    public function testCedilla(): void
    {
        Assert::equals('garcon', Slug::from('Garçon')->getValue());
    }

    public function testAlreadySlug(): void
    {
        Assert::equals('deja-un-slug', Slug::from('deja-un-slug')->getValue());
    }

    public function testToString(): void
    {
        Assert::equals('test', (string) Slug::from('Test'));
    }

    public function testNumbers(): void
    {
        Assert::equals('table-8-personnes', Slug::from('Table 8 personnes')->getValue());
    }
}
