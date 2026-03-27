<?php

/**
 * Déplace les 3 services impurs de Domain → Application/{module}/Service/
 * et met à jour tous les namespaces + use imports dans tout le projet
 * (src/, tests/, templates/).
 */

$base = dirname(__DIR__);

$moves = [
    [
        'from'         => 'src/Domain/Catalog/Service/SlugUniquenessService.php',
        'to'           => 'src/Application/Catalog/Service/SlugUniquenessService.php',
        'oldNamespace' => 'Rore\Domain\Catalog\Service',
        'newNamespace' => 'Rore\Application\Catalog\Service',
    ],
    [
        'from'         => 'src/Domain/Reservation/Service/AvailabilityService.php',
        'to'           => 'src/Application/Reservation/Service/AvailabilityService.php',
        'oldNamespace' => 'Rore\Domain\Reservation\Service',
        'newNamespace' => 'Rore\Application\Reservation\Service',
    ],
    [
        'from'         => 'src/Domain/Cart/Service/CartService.php',
        'to'           => 'src/Application/Cart/Service/CartService.php',
        'oldNamespace' => 'Rore\Domain\Cart\Service',
        'newNamespace' => 'Rore\Application\Cart\Service',
    ],
];

// ── 1. Déplacer + mettre à jour le namespace interne ──────────────────────
$renames = []; // oldFqcn => newFqcn (pour chaque classe)
foreach ($moves as $m) {
    $oldPath = "$base/{$m['from']}";
    $newPath = "$base/{$m['to']}";

    if (!file_exists($oldPath)) {
        echo "SKIP (introuvable) : {$m['from']}\n";
        continue;
    }

    $dir = dirname($newPath);
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $content = file_get_contents($oldPath);
    $content = str_replace(
        'namespace ' . $m['oldNamespace'] . ';',
        'namespace ' . $m['newNamespace'] . ';',
        $content
    );
    file_put_contents($newPath, $content);
    unlink($oldPath);

    // Mémoriser le renommage FQCN pour chaque classe du fichier
    $className = pathinfo($newPath, PATHINFO_FILENAME);
    $renames[$m['oldNamespace'] . '\\' . $className] = $m['newNamespace'] . '\\' . $className;

    echo "Déplacé : {$m['from']} → {$m['to']}\n";
}

// ── 2. Mettre à jour tous les imports dans src/, tests/, templates/ ────────
$allFiles = [];
foreach (['src', 'tests', 'templates'] as $dir) {
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
        $new = str_replace($old, $new_, $new);
    }
    if ($new !== $content) {
        file_put_contents($path, $new);
        $updated++;
        echo "  Mis à jour : " . str_replace($base . '/', '', $path) . "\n";
    }
}

echo "\n$updated fichiers consommateurs mis à jour.\nTerminé.\n";
