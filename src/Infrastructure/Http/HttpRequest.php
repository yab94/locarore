<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Http;

use Rore\Presentation\Http\RequestInterface;
use Rore\Infrastructure\Shared\ArrayTypedParams;

final class HttpRequest implements RequestInterface
{
    public string $method { get => (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'); }
    public ArrayTypedParams $queryString { get => new ArrayTypedParams($_GET); }
    public ArrayTypedParams $body { get => new ArrayTypedParams($_POST); }
    public ArrayTypedParams $server { get => new ArrayTypedParams($_SERVER); }
    public ArrayTypedParams $files { get => new ArrayTypedParams($_FILES); }
}
