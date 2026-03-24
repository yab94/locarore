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

    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    public function inputString(string $key, string $default = ''): string
    {
        return trim((string) $this->input($key, $default));
    }

    public function inputStringOrNull(string $key): ?string
    {
        $value = $this->inputString($key, '');
        return $value != '' ? $value : null;
    }

    public function inputInt(string $key, int $default = 0): int
    {
        return (int) $this->input($key, $default);
    }

    public function inputFloat(string $key, float $default = 0.0): float
    {
        return (float) $this->input($key, $default);
    }

    public function inputArray(string $key, array $default = []): array
    {
        $value = $this->input($key, $default);
        return is_array($value) ? $value : $default;
    }

    public function queryParams(): array
    {
        return is_array($_GET) ? $_GET : [];
    }

    public function postParams(): array
    {
        return is_array($_POST) ? $_POST : [];
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
