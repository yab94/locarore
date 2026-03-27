<?php

// Migration : couches Application/Domain/Infrastructure → modules par feature
// Application services → UseCase (helpers partagés entre use cases)
// CartService (session) → Cart/Adapter
// MySql* repos → Module/Adapter
// MysqlDatabase → Shared/Infrastructure

$base = dirname(__DIR__);

// ── Mapping : [from_glob_dir, to_dir, old_namespace, new_namespace] ────────
$dirMoves = [
    // Application → modules
    ['src/Application/Cart/UseCase',            'src/Cart/UseCase',            'Rore\Application\Cart\UseCase',            'Rore\Cart\UseCase'],
    ['src/Application/Cart/Service',            'src/Cart/Adapter',            'Rore\Application\Cart\Service',            'Rore\Cart\Adapter'],
    ['src/Application/Catalog/UseCase',         'src/Catalog/UseCase',         'Rore\Application\Catalog\UseCase',         'Rore\Catalog\UseCase'],
    ['src/Application/Catalog/Service',         'src/Catalog/UseCase',         'Rore\Application\Catalog\Service',         'Rore\Catalog\UseCase'],
    ['src/Application/Catalog/Port',            'src/Catalog/Port',            'Rore\Application\Catalog\Port',            'Rore\Catalog\Port'],
    ['src/Application/Contact/UseCase',         'src/Contact/UseCase',         'Rore\Application\Contact\UseCase',         'Rore\Contact\UseCase'],
    ['src/Application/Contact/Port',            'src/Contact/Port',            'Rore\Application\Contact\Port',            'Rore\Contact\Port'],
    ['src/Application/Reservation/UseCase',     'src/Reservation/UseCase',     'Rore\Application\Reservation\UseCase',     'Rore\Reservation\UseCase'],
    ['src/Application/Reservation/Service',     'src/Reservation/UseCase',     'Rore\Application\Reservation\Service',     'Rore\Reservation\UseCase'],
    ['src/Application/Reservation/Port',        'src/Reservation/Port',        'Rore\Application\Reservation\Port',        'Rore\Reservation\Port'],
    ['src/Application/Search/UseCase',          'src/Search/UseCase',          'Rore\Application\Search\UseCase',          'Rore\Search\UseCase'],
    ['src/Application/Search/Port',             'src/Search/Port',             'Rore\Application\Search\Port',             'Rore\Search\Port'],
    ['src/Application/Settings/UseCase',        'src/Settings/UseCase',        'Rore\Application\Settings\UseCase',        'Rore\Settings\UseCase'],
    ['src/Application/Settings/Port',           'src/Settings/Port',           'Rore\Application\Settings\Port',           'Rore\Settings\Port'],

    // Domain → modules
    ['src/Domain/Cart/ValueObject',             'src/Cart/ValueObject',        'Rore\Domain\Cart\ValueObject',             'Rore\Cart\ValueObject'],
    ['src/Domain/Catalog/Entity',               'src/Catalog/Entity',          'Rore\Domain\Catalog\Entity',               'Rore\Catalog\Entity'],
    ['src/Domain/Catalog/Service',              'src/Catalog/Service',         'Rore\Domain\Catalog\Service',              'Rore\Catalog\Service'],
    ['src/Domain/Catalog/ValueObject',          'src/Catalog/ValueObject',     'Rore\Domain\Catalog\ValueObject',          'Rore\Catalog\ValueObject'],
    ['src/Domain/Contact/Entity',               'src/Contact/Entity',          'Rore\Domain\Contact\Entity',               'Rore\Contact\Entity'],
    ['src/Domain/Reservation/Entity',           'src/Reservation/Entity',      'Rore\Domain\Reservation\Entity',           'Rore\Reservation\Entity'],
    ['src/Domain/Settings/Entity',              'src/Settings/Entity',         'Rore\Domain\Settings\Entity',              'Rore\Settings\Entity'],
    ['src/Domain/Shared/ValueObject',           'src/Shared/ValueObject',      'Rore\Domain\Shared\ValueObject',           'Rore\Shared\ValueObject'],
];

// Fichiers individuels (Infrastructure → modules + Domain/Cart root)
$fileMoves = [
    ['src/Domain/Cart/CartState.php',                                    'src/Cart/CartState.php',                                   'Rore\Domain\Cart',             'Rore\Cart'],
    ['src/Infrastructure/Database/MysqlDatabase.php',                   'src/Shared/Infrastructure/MysqlDatabase.php',              'Rore\Infrastructure\Database', 'Rore\Shared\Infrastructure'],
    ['src/Infrastructure/Persistence/MySqlCategoryRepository.php',      'src/Catalog/Adapter/MySqlCategoryRepository.php',          'Rore\Infrastructure\Persistence', 'Rore\Catalog\Adapter'],
    ['src/Infrastructure/Persistence/MySqlPackRepository.php',          'src/Catalog/Adapter/MySqlPackRepository.php',              'Rore\Infrastructure\Persistence', 'Rore\Catalog\Adapter'],
    ['src/Infrastructure/Persistence/MySqlProductRepository.php',       'src/Catalog/Adapter/MySqlProductRepository.php',           'Rore\Infrastructure\Persistence', 'Rore\Catalog\Adapter'],
    ['src/Infrastructure/Persistence/MySqlTagRepository.php',           'src/Catalog/Adapter/MySqlTagRepository.php',               'Rore\Infrastructure\Persistence', 'Rore\Catalog\Adapter'],
    ['src/Infrastructure/Persistence/MySqlSearchRepository.php',        'src/Search/Adapter/MySqlSearchRepository.php',             'Rore\Infrastructure\Persistence', 'Rore\Search\Adapter'],
    ['src/Infrastructure/Persistence/MySqlContactMessageRepository.php','src/Contact/Adapter/MySqlContactMessageRepository.php',    'Rore\Infrastructure\Persistence', 'Rore\Contact\Adapter'],
    ['src/Infrastructure/Persistence/MySqlReservationRepository.php',   'src/Reservation/Adapter/MySqlReservationRepository.php',   'Rore\Infrastructure\Persistence', 'Rore\Reservation\Adapter'],
    ['src/Infrastructure/Persistence/MySqlSettingsRepository.php',      'src/Settings/Adapter/MySqlSettingsRepository.php',         'Rore\Infrastructure\Persistence', 'Rore\Settings\Adapter'],
];

// ── 1. Déplacer les dossiers ───────────────────────────────────────────────
$renames = []; // old_fqcn_prefix => new_fqcn_prefix (pour str_replace)

foreach ($dirMoves as [$fromDir, $toDir, $oldNs, $newNs]) {
    $srcDir = "$base/$fromDir";
    if (!is_dir($srcDir)) { echo "SKIP dir : $fromDir\n"; continue; }
    $destDir = "$base/$toDir";
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);

    $files = glob("$srcDir/*.php");
    foreach ($files as $oldPath) {
        $filename = basename($oldPath);
        $newPath  = "$destDir/$filename";
        $content  = file_get_contents($oldPath);
        $content  = str_replace("namespace $oldNs;", "namespace $newNs;", $content);
        file_put_contents($newPath, $content);
        unlink($oldPath);
        echo "  Déplacé : $fromDir/$filename → $toDir/$filename\n";
    }
    // Supprimer dossier source si vide
    if (is_dir($srcDir) && count(array_diff(scandir($srcDir), ['.','..'])) === 0) {
        rmdir($srcDir);
    }

    $renames[$oldNs] = $newNs;
}

// ── 2. Déplacer les fichiers individuels ──────────────────────────────────
foreach ($fileMoves as [$from, $to, $oldNs, $newNs]) {
    $oldPath = "$base/$from";
    if (!file_exists($oldPath)) { echo "SKIP file : $from\n"; continue; }
    $newPath = "$base/$to";
    $dir = dirname($newPath);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $content = file_get_contents($oldPath);
    $content = str_replace("namespace $oldNs;", "namespace $newNs;", $content);
    file_put_contents($newPath, $content);
    unlink($oldPath);
    echo "  Déplacé : $from → $to\n";
    $renames[$oldNs] = $newNs;
}

// Supprimer arborescences vides dans Application, Domain, Infrastructure
function removeEmptyDirs(string $dir): void {
    if (!is_dir($dir)) return;
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = "$dir/$item";
        if (is_dir($path)) removeEmptyDirs($path);
    }
    if (count(array_diff(scandir($dir), ['.','..'])) === 0) {
        rmdir($dir);
        echo "  rmdir : " . basename($dir) . "\n";
    }
}
foreach (['src/Application', 'src/Domain', 'src/Infrastructure/Persistence'] as $d) {
    removeEmptyDirs("$base/$d");
}

// ── 3. Mettre à jour tous les imports ─────────────────────────────────────
// Trier par longueur décroissante pour éviter les remplacements partiels
uksort($renames, fn($a, $b) => strlen($b) - strlen($a));

$scanDirs = ['src', 'tests', 'templates'];
$allFiles = [];
foreach ($scanDirs as $d) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$base/$d"));
    foreach ($it as $f) {
        if ($f->isFile() && $f->getExtension() === 'php') $allFiles[] = $f->getPathname();
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
        echo "  Updated : " . str_replace("$base/", '', $path) . "\n";
    }
}

echo "\n$updated fichiers consommateurs mis à jour.\nTerminé.\n";
