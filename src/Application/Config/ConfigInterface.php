<?php

declare(strict_types=1);

namespace Rore\Application\Config;

interface ConfigInterface
{
    public function get(string $path, mixed $default = null): mixed;
    public function getString(string $key, string $default = ''): string;
    public function getInt(string $key, int $default = 0): int;
    public function getFloat(string $key, float $default = 0.0): float;
    public function getArray(string $path, array $default = []): array;
}
