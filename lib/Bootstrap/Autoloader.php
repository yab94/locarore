<?php

namespace RRB\Bootstrap;

class Autoloader
{
    public static function register(string $prefix, string $baseDir): void
    {
        spl_autoload_register(function (string $class) use ($prefix, $baseDir): void {
            if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;
            $relative = substr($class, strlen($prefix));
            $file = $baseDir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
        });
    }
}