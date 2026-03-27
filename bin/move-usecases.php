<?php

/**
 * Déplace tous les *UseCase.php vers Application/{Module}/UseCase/
 * et met à jour tous les namespaces + use imports dans tout le projet.
 */

$base = dirname(__DIR__);

// 1. Trouver tous les *UseCase.php dans Application (hors sous-dossier UseCase déjà)
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$base/src/Application"));
$useCaseFiles = [];
foreach ($files as $f) {
    if (!$f->isFile() || $f->getExtension() !== 'php') continue;
    if (!str_ends_with($f->getFilename(), 'UseCase.php')) continue;
    // déjà dans un sous-dossier UseCase ?
    if (str_contains($f->getPathname(), '/UseCase/')) continue;
    $useCaseFiles[] = $f->getPathname();
}

echo count($useCaseFiles) . " fichiers UseCase à déplacer\n";

// 2. Construire la map oldNamespace => newNamespace + déplacer
$renames = []; // oldFqcn => newFqcn
foreach ($useCaseFiles as $oldPath) {
    $content = file_get_contents($oldPath);
    if (!preg_match('/^namespace\s+([\w\\\\]+)\s*;/m', $content, $m)) continue;
    $oldNamespace = $m[1];

    // Nouveau namespace = oldNamespace\UseCase
    $newNamespace = $oldNamespace . '\\UseCase';

    // Nouveau chemin
    $moduleDir = dirname($oldPath);
    $filename  = basename($oldPath);
    $newDir    = $moduleDir . '/UseCase';
    $newPath   = $newDir . '/' . $filename;

    if (!is_dir($newDir)) mkdir($newDir, 0755, true);

    // Mettre à jour le namespace dans le fichier
    $newContent = preg_replace(
        '/^namespace\s+' . preg_quote($oldNamespace, '/') . '\s*;/m',
        'namespace ' . $newNamespace . ';',
        $content
    );

    file_put_contents($newPath, $newContent);
    unlink($oldPath);

    // Stocker le renommage des FQCN
    $className = pathinfo($filename, PATHINFO_FILENAME);
    $renames[$oldNamespace . '\\' . $className] = $newNamespace . '\\' . $className;

    echo "  Déplacé : $filename  ($oldNamespace → $newNamespace)\n";
}

// 3. Mettre à jour tous les use imports dans tout le projet
echo "\nMise à jour des imports dans src/ et tests/...\n";

$allFiles = [];
foreach (['src', 'tests'] as $dir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$base/$dir"));
    foreach ($it as $f) {
        if ($f->isFile() && $f->getExtension() === 'php') {
            $allFiles[] = $f->getPathname();
        }
    }
}

$updated = 0;
foreach ($allFiles as $path) {
    $content = file_get_contents($path);
    $new = $content;
    foreach ($renames as $old => $new_) {
        // Remplacer "use Old\Fqcn;" → "use New\Fqcn;"
        $new = str_replace('use ' . $old . ';', 'use ' . $new_ . ';', $new);
        // Remplacer dans les use groupés ou autres occurrences de type-hint (rare mais possible)
        $new = str_replace($old, $new_, $new);
    }
    if ($new !== $content) {
        file_put_contents($path, $new);
        $updated++;
        echo "  Mis à jour : " . str_replace($base . '/', '', $path) . "\n";
    }
}

echo "\n$updated fichiers consommateurs mis à jour.\nTerminé.\n";
