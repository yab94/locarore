<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Http;

use Rore\Presentation\Http\RequestInterface;
use Rore\Infrastructure\Shared\ArrayTypedParams;

final class HttpRequest implements RequestInterface
{
    public ArrayTypedParams $queryString { get => new ArrayTypedParams($_GET); }
    public ArrayTypedParams $body { get => new ArrayTypedParams($_POST); }
    public ArrayTypedParams $server { get => new ArrayTypedParams($_SERVER); }
    public ArrayTypedParams $files { get => new ArrayTypedParams($_FILES); }
    public string $method { get => (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'); }

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
}
