<?php

declare(strict_types=1);

namespace Rore\Presentation\Http;

use Rore\Support\Typable;

interface RequestInterface
{
    public string $method {  get; }
    public Typable $queryString {  get; }
    public Typable $body {  get; }
    public Typable $server {  get; }
    public Typable $files {  get; }
}
