<?php

declare(strict_types=1);

namespace Rore\Catalog\ValueObject;

final class Slug
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = self::slugify($value);
    }

    public static function from(string $text): self
    {
        return new self($text);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function slugify(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = strtr($text, [
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a',
            'ô' => 'o', 'ö' => 'o', 'ó' => 'o',
            'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ú' => 'u',
            'î' => 'i', 'ï' => 'i', 'í' => 'i',
            'ç' => 'c', 'ñ' => 'n',
        ]);
        $text = preg_replace('/[^a-z0-9\s\-]/', '', $text);
        $text = preg_replace('/[\s\-]+/', '-', trim($text ?? ''));
        return $text ?? '';
    }
}
