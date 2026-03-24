<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Http;

use Rore\Presentation\Http\RequestInterface;
use Rore\Infrastructure\Shared\ArrayTypedParams;

final class HttpRequest implements RequestInterface
{
    public ArrayTypedParams $queryString { get => new ArrayTypedParams($_GET); }
    public ArrayTypedParams $body { get => new ArrayTypedParams($_POST); }

    public function method(): string
    {
        return (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function file(string $key): ?array
    {
        $f = $_FILES[$key] ?? null;
        if ($f === null) {
            return null;
        }

        // Single file upload
        if (isset($f['error']) && !is_array($f['error'])) {
            return $f['error'] !== UPLOAD_ERR_NO_FILE ? $f : null;
        }

        // Multiple files upload (name[] syntax)
        return (!empty($f['name'][0])) ? $f : null;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $_SERVER[$key] ?? $default;
    }
}
