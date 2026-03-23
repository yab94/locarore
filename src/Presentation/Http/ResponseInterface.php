<?php

declare(strict_types=1);

namespace Rore\Presentation\Http;

interface ResponseInterface
{
    public function setStatusCode(int $code): void;

    public function header(string $name, string $value, bool $replace = true): void;

    public function redirect(string $url, int $statusCode = 302): void;

    public function write(string $content): void;
}
