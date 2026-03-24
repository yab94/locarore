<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Config;

use Rore\Infrastructure\Shared\AbstractTypedParams;

final class Config extends AbstractTypedParams
{
    public function __construct(private readonly array $data)
    {
    }

    public function getParam(string $path, mixed $default = null): mixed
    {
        $path = trim($path);
        if ($path === '') {
            return $default;
        }

        $cursor = $this->data;
        foreach (explode('.', $path) as $segment) {
            if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
                return $default;
            }
            $cursor = $cursor[$segment];
        }

        return $cursor;
    }
}
