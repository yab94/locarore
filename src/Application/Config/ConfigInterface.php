<?php

declare(strict_types=1);

namespace Rore\Application\Config;

interface ConfigInterface
{
    public function getParam(string $path, mixed $default = null): mixed;
    public function getStringParam(string $key, string $default = ''): string;
    public function getIntParam(string $key, int $default = 0): int;
    public function getFloatParam(string $key, float $default = 0.0): float;
    public function getArrayParam(string $path, array $default = []): array;
    public function isProduction(): bool;
}
