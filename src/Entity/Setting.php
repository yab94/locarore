<?php

declare(strict_types=1);

namespace Rore\Entity;

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

    /**
     * Retourne la valeur avec les variables interpolées.
     * Remplace {key} par la valeur correspondante dans $vars.
     *
     * @param array<string,string> $vars
     */
    public function resolve(array $vars = []): string
    {
        $value = $this->value ?? '';
        foreach ($vars as $k => $v) {
            $value = str_replace('{' . $k . '}', (string) $v, $value);
        }
        return $value;
    }
}
