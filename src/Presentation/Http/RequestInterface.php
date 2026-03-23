<?php

declare(strict_types=1);

namespace Rore\Presentation\Http;

interface RequestInterface
{
    public function method(): string;

    public function query(string $key, mixed $default = null): mixed;

    public function post(string $key, mixed $default = null): mixed;

    public function input(string $key, mixed $default = null): mixed;

    /** @return array<mixed> */
    public function queryParams(): array;

    /** @return array<mixed> */
    public function postParams(): array;

    /** @return array<mixed>|null $_FILES entry or null if absent/empty */
    public function file(string $key): ?array;

    public function server(string $key, mixed $default = null): mixed;
}
