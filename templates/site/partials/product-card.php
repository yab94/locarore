<?php
$_productUrl = isset($productContextPath)
    ? '/produit/' . $productContextPath . '/' . $product->getSlug()
    : productCanonicalUrl($product, $allCategories ?? []);
?>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
    <?php if ($photo = $product->getMainPhoto()): ?>
        <img src="<?= e($photo->getPublicPath()) ?>"
             alt="<?= e($product->getName()) ?>"
             class="w-full h-48 object-cover">
    <?php else: ?>
        <div class="w-full h-48 bg-gray-100 flex items-center justify-center text-gray-400 text-sm">
            Pas de photo
        </div>
    <?php endif; ?>
    <div class="p-4">
        <h3 class="font-semibold text-gray-800 mb-1"><?= e($product->getName()) ?></h3>
        <p class="text-sm text-gray-500 mb-3 line-clamp-2"><?= e($product->getDescription() ?? '') ?></p>
        <div class="flex items-center justify-between">
            <span class="text-brand-700 font-bold">
                à partir de <?= number_format($product->getPriceBase(), 0, ',', ' ') ?> €
            </span>
            <a href="<?= e($_productUrl) ?>"
               class="text-sm bg-brand-600 text-white px-3 py-1.5 rounded-lg hover:bg-brand-700 transition">
                Voir
            </a>
        </div>
    </div>
</div>
