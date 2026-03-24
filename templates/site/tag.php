<!-- Fil d'ariane -->
<nav class="text-sm text-gray-500 mb-6 flex flex-wrap items-center gap-1">
    <a href="/" class="hover:underline">Accueil</a>
    <span>›</span>
    <span class="text-gray-800 font-medium"><?= $html($tag->getName()) ?></span>
</nav>

<div class="mb-8">
    <div class="flex items-center gap-3 mb-1">
        <span class="text-2xl">🏷️</span>
        <h1 class="text-3xl font-bold text-gray-900"><?= $html($tag->getName()) ?></h1>
    </div>
    <p class="text-gray-500 mt-2">
        <?= count($products) ?> article<?= count($products) !== 1 ? 's' : '' ?> disponible<?= count($products) !== 1 ? 's' : '' ?>
    </p>
</div>

<!-- Produits -->
<?php if (empty($products)): ?>
    <div class="text-center py-16 text-gray-400">
        <p>Aucun produit disponible pour ce tag.</p>
        <a href="/" class="mt-4 inline-block text-brand-600 hover:underline">Retour à l'accueil</a>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($products as $product): ?>
            <?php include 'partials/product-card.php'; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
