<?php

declare(strict_types=1);

namespace Rore\Presentation\Http;

use Rore\Infrastructure\Shared\ArrayTypedParams;

interface RequestInterface
{
    public string $method {  get; }
    public ArrayTypedParams $queryString {  get; }
    public ArrayTypedParams $body {  get; }
    public ArrayTypedParams $server {  get; }
    public ArrayTypedParams $files {  get; }

}
