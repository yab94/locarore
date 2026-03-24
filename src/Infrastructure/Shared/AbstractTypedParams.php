<?php

namespace Rore\Infrastructure\Shared;

abstract class AbstractTypedParams
{
    abstract public function getParam(string $key, mixed $default = null): mixed;

    public function getArrayParam(string $path, array $default = []): array
    {
        $value = $this->getParam($path);
        if (!is_array($value)) {
            throw new \InvalidArgumentException("Param '$path' is not an array");
        }

        return $value ?? $default;
    }

    public function getIntParam(string $key, int $default = 0): int
    {
        $value = $this->getParam($key);
        if ($value === null) {
            return $default;
        }
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException("Param '$key' is not numeric");
        }
        return (int)$value;
    }

    public function getFloatParam(string $key, float $default = 0.0): float
    {
        $value = $this->getParam($key);
        if ($value === null) {
            return $default;
        }
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException("Param '$key' is not numeric");
        }
        return (float)$value;
    }

    public function getStringParam(string $key, string $default = ''): string
    {
        $value = $this->getParam($key);
        if ($value === null) {
            return $default;
        }
        if (!is_string($value)) {
            throw new \InvalidArgumentException("Param '$key' is not a string");
        }
        return $value;
    }
}