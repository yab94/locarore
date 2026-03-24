<?php

namespace Rore\Infrastructure\Shared;

class ArrayTypedParams extends AbstractTypedParams
{
    public function __construct(readonly array $params)
    {
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getParam(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }
}