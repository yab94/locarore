<!-- Fil d'ariane -->
<nav class="text-sm text-gray-500 mb-6 flex flex-wrap items-center gap-1">
    <a href="/" class="hover:underline">Accueil</a>
    <?php foreach ($breadcrumb as $crumb): ?>
        <span>›</span>
        <?php if ($crumb->getId() !== $category->getId()): ?>
            <a href="/categorie/<?= e($crumb->getSlug()) ?>" class="hover:underline">
                <?= e($crumb->getName()) ?>
            </a>
        <?php else: ?>
            <span class="text-gray-800 font-medium"><?= e($crumb->getName()) ?></span>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900"><?= e($category->getName()) ?></h1>
    <?php if ($category->getDescription()): ?>
        <div class="text-gray-600 mt-2 prose prose-sm max-w-none">
            <?= $category->getDescription() /* Markdown stocké — affiché brut, peut être rendu côté client */ ?>
        </div>
    <?php endif; ?>
</div>

<!-- Sous-catégories -->
<?php if (!empty($children)): ?>
<section class="mb-10">
    <h2 class="text-lg font-semibold text-gray-700 mb-4">Sous-catégories</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($children as $child): ?>
            <a href="/categorie/<?= e($slugPath ? $slugPath . '/' . $child->getSlug() : $child->getSlug()) ?>"
               class="bg-white border border-gray-200 rounded-xl p-4 hover:border-brand-600 hover:shadow-sm transition text-center">
                <div class="text-2xl mb-2">🏷️</div>
                <div class="text-sm font-medium text-gray-800"><?= e($child->getName()) ?></div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Produits -->
<?php if (empty($products)): ?>
    <div class="text-center py-16 text-gray-400">
        <p>Aucun produit disponible dans cette catégorie.</p>
        <a href="/" class="mt-4 inline-block text-brand-600 hover:underline">Retour à l'accueil</a>
    </div>
<?php else: ?>
    <h2 class="text-lg font-semibold text-gray-700 mb-4">Produits</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($products as $product): ?>
            <?php include BASE_PATH . '/templates/site/partials/product-card.php'; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
