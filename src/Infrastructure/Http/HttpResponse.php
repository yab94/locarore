<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Http;

use Rore\Presentation\Http\ResponseInterface;

final class HttpResponse implements ResponseInterface
{
    public function setStatusCode(int $code): void
    {
        http_response_code($code);
    }

    public function header(string $name, string $value, bool $replace = true): void
    {
        header($name . ': ' . $value, $replace);
    }

    public function redirect(string $url, int $statusCode = 302): void
    {
        header('Location: ' . $url, true, $statusCode);
    }

    public function write(string $content): void
    {
        echo $content;
    }
}
