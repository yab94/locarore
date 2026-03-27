<?php

// Déplace toutes les interfaces de repo de Domain/{module}/Repository/ vers Application/{module}/Port/
// et met à jour tous les imports dans src/, tests/, templates/.

$base = dirname(__DIR__);

$moves = [
    // Catalog
    ['from' => 'src/Domain/Catalog/Repository/CategoryRepositoryInterface.php',   'to' => 'src/Application/Catalog/Port/CategoryRepositoryInterface.php',   'oldNs' => 'Rore\Domain\Catalog\Repository',   'newNs' => 'Rore\Application\Catalog\Port'],
    ['from' => 'src/Domain/Catalog/Repository/PackRepositoryInterface.php',       'to' => 'src/Application/Catalog/Port/PackRepositoryInterface.php',       'oldNs' => 'Rore\Domain\Catalog\Repository',   'newNs' => 'Rore\Application\Catalog\Port'],
    ['from' => 'src/Domain/Catalog/Repository/ProductRepositoryInterface.php',    'to' => 'src/Application/Catalog/Port/ProductRepositoryInterface.php',    'oldNs' => 'Rore\Domain\Catalog\Repository',   'newNs' => 'Rore\Application\Catalog\Port'],
    ['from' => 'src/Domain/Catalog/Repository/SearchRepositoryInterface.php',     'to' => 'src/Application/Search/Port/SearchRepositoryInterface.php',      'oldNs' => 'Rore\Domain\Catalog\Repository',   'newNs' => 'Rore\Application\Search\Port'],
    ['from' => 'src/Domain/Catalog/Repository/TagRepositoryInterface.php',        'to' => 'src/Application/Catalog/Port/TagRepositoryInterface.php',        'oldNs' => 'Rore\Domain\Catalog\Repository',   'newNs' => 'Rore\Application\Catalog\Port'],
    // Contact
    ['from' => 'src/Domain/Contact/Repository/ContactMessageRepositoryInterface.php', 'to' => 'src/Application/Contact/Port/ContactMessageRepositoryInterface.php', 'oldNs' => 'Rore\Domain\Contact\Repository',   'newNs' => 'Rore\Application\Contact\Port'],
    // Reservation
    ['from' => 'src/Domain/Reservation/Repository/ReservationRepositoryInterface.php', 'to' => 'src/Application/Reservation/Port/ReservationRepositoryInterface.php', 'oldNs' => 'Rore\Domain\Reservation\Repository', 'newNs' => 'Rore\Application\Reservation\Port'],
    // Settings
    ['from' => 'src/Domain/Settings/Repository/SettingsRepositoryInterface.php',  'to' => 'src/Application/Settings/Port/SettingsRepositoryInterface.php',  'oldNs' => 'Rore\Domain\Settings\Repository',  'newNs' => 'Rore\Application\Settings\Port'],
];

// ── 1. Déplacer + mettre à jour le namespace interne ──────────────────────
$renames = [];
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
        'namespace ' . $m['oldNs'] . ';',
        'namespace ' . $m['newNs'] . ';',
        $content
    );
    file_put_contents($newPath, $content);
    unlink($oldPath);

    $className = pathinfo($newPath, PATHINFO_FILENAME);
    $renames[$m['oldNs'] . '\\' . $className] = $m['newNs'] . '\\' . $className;

    echo "Déplacé : {$m['from']} → {$m['to']}\n";
}

// ── 2. Supprimer les dossiers vides ───────────────────────────────────────
$emptyDirs = [
    'src/Domain/Catalog/Repository',
    'src/Domain/Contact/Repository',
    'src/Domain/Reservation/Repository',
    'src/Domain/Settings/Repository',
];
foreach ($emptyDirs as $dir) {
    $path = "$base/$dir";
    if (is_dir($path) && count(scandir($path)) === 2) {
        rmdir($path);
        echo "Supprimé dossier vide : $dir\n";
    }
}

// ── 3. Mettre à jour tous les imports ─────────────────────────────────────
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
