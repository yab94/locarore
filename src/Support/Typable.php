<?php

declare(strict_types=1);

namespace Rore\Support;

use InvalidArgumentException;

class Typable
{
    public function __construct(protected array $data)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    final public function getArray(string $path, array $default = []): array
    {
        $value = $this->get($path, $default);
        if (!is_array($value)) {
            throw new \InvalidArgumentException("Param '$path' is not an array");
        }

        return $value;
    }

    final public function getInt(string $key, int $default = 0): int
    {
        $value = $this->get($key, $default);
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException("Param '$key' is not numeric");
        }
        return (int)$value;
    }

    final public function getFloat(string $key, float $default = 0.0): float
    {
        $value = $this->get($key, $default);
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException("Param '$key' is not numeric");
        }
        return (float)$value;
    }

    final public function getString(string $key, string $default = ''): string
    {
        $value = $this->get($key, $default);
        if (!is_string($value)) {
            throw new \InvalidArgumentException("Param '$key' is not a string");
        }
        return $value;
    }
}
