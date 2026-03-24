<!-- Fil d'ariane -->
<nav class="text-sm text-gray-500 mb-6 flex flex-wrap items-center gap-1">
    <a href="/" class="hover:underline">Accueil</a>
    <?php foreach ($breadcrumb as $crumb): ?>
        <span>›</span>
        <?php if ($crumb->getId() !== $category->getId()): ?>
            <a href="<?= $html($urlResolver->categoryUrl($crumb, $allCategories)) ?>" class="hover:underline">
                <?= $html($crumb->getName()) ?>
            </a>
        <?php else: ?>
            <span class="text-gray-800 font-medium"><?= $html($crumb->getName()) ?></span>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900"><?= $html($category->getName()) ?></h1>
    <?php if ($category->getDescriptionShort()): ?>
        <p class="text-gray-500 mt-2 text-base"><?= $html($category->getDescriptionShort()) ?></p>
    <?php endif; ?>
</div>

<!-- Sous-catégories -->
<?php if (!empty($children)): ?>
<section class="mb-10">
    <h2 class="text-lg font-semibold text-gray-700 mb-4">Sous-catégories</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($children as $child): ?>
            <a href="<?= $html($urlResolver->categoryUrl($child, $allCategories)) ?>"
               class="bg-white border border-gray-200 rounded-xl p-4 hover:border-brand-600 hover:shadow-sm transition text-center">
                <div class="text-2xl mb-2">🏷️</div>
                <div class="text-sm font-medium text-gray-800"><?= $html($child->getName()) ?></div>
                <?php if ($child->getDescriptionShort()): ?>
                    <div class="text-xs text-gray-500 mt-1 line-clamp-2"><?= $html($child->getDescriptionShort()) ?></div>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Description longue -->
<?php if ($category->getDescription()): ?>
<div class="prose prose-sm max-w-none text-gray-700 mb-8">
    <?= $category->getDescription() ?>
</div>
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
        <?php
        $productContextPath = $urlResolver->categoryPath($category, $allCategories);
        foreach ($products as $product): ?>
            <?php include 'partials/product-card.php'; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($packs)): ?>
<section class="mt-12">
    <h2 class="text-lg font-semibold text-gray-700 mb-4">Packs incluant ces articles</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($packs as $pack): ?>
            <?php include 'partials/pack-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
