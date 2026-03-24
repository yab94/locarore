<?php

declare(strict_types=1);

namespace Rore\Presentation\Http;

use Rore\Infrastructure\Shared\ArrayTypedParams;

interface RequestInterface
{
    public string $method {  get; }
    public ArrayTypedParams $queryString {  get; }
    public ArrayTypedParams $body {  get; }

    public function file(string $key): ?array;
    public function server(string $key, mixed $default = null): mixed;
}
