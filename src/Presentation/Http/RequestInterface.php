<?php

declare(strict_types=1);

namespace Rore\Presentation\Http;

use Rore\Infrastructure\Shared\ArrayTypedParams;

interface RequestInterface
{
    public ArrayTypedParams $queryString {  get; }
    public ArrayTypedParams $body {  get; }

    /** @return array<mixed>|null $_FILES entry or null if absent/empty */
    public function file(string $key): ?array;

    public function server(string $key, mixed $default = null): mixed;
}
