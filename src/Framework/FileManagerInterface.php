<?php

declare(strict_types=1);

namespace Rore\Framework;

interface FileManagerInterface
{
    /**
     * Traite un fichier uploadé et retourne son nom final.
     *
     * @param  array $file  Entrée $_FILES['field']
     * @return string       Nom du fichier (sans chemin)
     * @throws \RuntimeException
     */
    public function upload(array $file): string;

    /**
     * Supprime un fichier par son nom (sans chemin).
     */
    public function delete(string $filename): void;
}
