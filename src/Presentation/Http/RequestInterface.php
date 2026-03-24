<?php

declare(strict_types=1);

namespace Rore\Presentation\Http;

use Rore\Infrastructure\Shared\ArrayTypedParams;

interface RequestInterface
{
    public ArrayTypedParams $queryString {  get; }
    public ArrayTypedParams $body {  get; }
    public function method(): string;
    public function file(string $key): ?array;
    public function server(string $key, mixed $default = null): mixed;
}
