<?php

declare(strict_types=1);

namespace Rore\Presentation\Http;

use Rore\Infrastructure\Shared\ArrayTypedParams;

interface RequestInterface
{
    public ArrayTypedParams $queryString {  get; }
    public ArrayTypedParams $body {  get; }

    public function query(string $key, mixed $default = null): mixed;

    public function post(string $key, mixed $default = null): mixed;

    public function input(string $key, mixed $default = null): mixed;

    public function inputString(string $key, string $default = ''): string;

    public function inputStringOrNull(string $key): ?string;

    public function inputInt(string $key, int $default = 0): int;

    public function inputFloat(string $key, float $default = 0.0): float;

    /** @return array<mixed> */
    public function inputArray(string $key, array $default = []): array;

    /** @return array<mixed> */
    public function queryParams(): array;

    /** @return array<mixed> */
    public function postParams(): array;

    /** @return array<mixed>|null $_FILES entry or null if absent/empty */
    public function file(string $key): ?array;

    public function server(string $key, mixed $default = null): mixed;
}
