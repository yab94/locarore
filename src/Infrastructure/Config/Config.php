<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Config;

/**
 * Value object de configuration (issu de config/app.ini).
 *
 * Accès recommandé via getParam("section.key", $default).
 */
final class Config
{
    /** @param array<string, mixed> $data */
    public function __construct(private readonly array $data)
    {
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Accède à un paramètre via chemin dot-notation.
     *
     * Ex: getParam('admin.password') ou getParam('upload.max_size', 5242880)
     */
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

    /** @return array<string, mixed> */
    public function section(string $name): array
    {
        $section = $this->getParam($name, []);
        return is_array($section) ? $section : [];
    }
}
