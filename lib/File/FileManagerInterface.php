<?php

declare(strict_types=1);

namespace RRB\File;

interface FileManagerInterface
{
    public function getMimeType(string $relativePath): string;
    public function getExtension(string $relativePath): string;
    public function delete(string $relativePath): void;
    public function rename(string $oldRelativePath, string $newRelativePath): void;
}
