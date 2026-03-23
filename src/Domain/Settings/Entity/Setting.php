<?php

declare(strict_types=1);

namespace Rore\Domain\Settings\Entity;

class Setting
{
    public function __construct(
        private string  $key,
        private ?string $value,
        private string  $label,
        private string  $type,   // 'text' | 'richtext'
        private string  $group,
    ) {}

    public function getKey(): string    { return $this->key; }
    public function getValue(): ?string { return $this->value; }
    public function getLabel(): string  { return $this->label; }
    public function getType(): string   { return $this->type; }
    public function getGroup(): string  { return $this->group; }

    public function setValue(?string $v): void { $this->value = $v; }
}
