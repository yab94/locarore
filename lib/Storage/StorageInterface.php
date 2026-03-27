<?php

declare(strict_types=1);

namespace RRB\Storage;

interface StorageInterface
{
    public function get(string $key, mixed $default = null): mixed;
    public function set(string $key, mixed $value): void;
    public function has(string $key): bool;
    public function remove(string $key): void;
}
