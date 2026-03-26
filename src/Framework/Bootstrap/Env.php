<?php

namespace Rore\Framework\Bootstrap;

class Env
{
    public static function load(string $baseDir): void
    {
        $envFile = $baseDir . '/.env';
        if (file_exists($envFile)) {
            foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$k, $v] = explode('=', $line, 2);
                $_ENV[trim($k)] = trim($v);
                putenv(trim($k) . '=' . trim($v));
            }
        }
    }
}