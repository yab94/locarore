<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Http;

use Rore\Presentation\Http\RequestInterface;
use Rore\Support\Typable;

final class HttpRequest implements RequestInterface
{
    public string $method { get => (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'); }
    public Typable $queryString { get => new Typable($_GET); }
    public Typable $body { get => new Typable($_POST); }
    public Typable $server { get => new Typable($_SERVER); }
    public Typable $files { get => new Typable($_FILES); }
}
