<?php

declare(strict_types=1);

namespace Rore\Framework;

use InvalidArgumentException;

class Typable
{
    private static object $MISSING;

    public function __construct(protected array $data) {}

    public static function missing(): object
    {
        return self::$MISSING ??= new \stdClass();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->data[$key] ?? self::missing();
        if ($value === self::$MISSING) {
            if (func_num_args() < 2) {
                throw new \InvalidArgumentException("Param '$key' is required.");
            }
            return $default;
        }
        return $value;
    }

    final public function getArray(string $path, ?array $default = null): array
    {
        $value = $default !== null ? $this->get($path, $default) : $this->get($path);
        if (!is_array($value)) {
            throw new \InvalidArgumentException("Param '$path' is not an array");
        }
        return $value;
    }

    final public function getInt(string $key, ?int $default = null): int
    {
        $value = $default !== null ? $this->get($key, $default) : $this->get($key);
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException("Param '$key' is not numeric");
        }
        return (int) $value;
    }

    final public function getFloat(string $key, ?float $default = null): float
    {
        $value = $default !== null ? $this->get($key, $default) : $this->get($key);
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException("Param '$key' is not numeric");
        }
        return (float) $value;
    }

    final public function getString(string $key, ?string $default = null): string
    {
        $value = $default !== null ? $this->get($key, $default) : $this->get($key);
        if (!is_string($value)) {
            throw new \InvalidArgumentException("Param '$key' is not a string");
        }
        return $value;
    }
}
