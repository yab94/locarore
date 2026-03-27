<?php

declare(strict_types=1);

namespace Rore\Framework\Storage;

interface FileManagerInterface
{
    /**
     * Valide et déplace le fichier uploadé.
     *
     * @param  array $file  Entrée $_FILES['field']
     * @return string       Chemin absolu du fichier déposé
     * @throws \RuntimeException
     */
    public function upload(array $file): string;

    /**
     * Supprime un fichier par son nom (sans chemin).
     */
    public function delete(string $filename): void;
}
