<?php
use Rore\Framework\View\HtmlEncoder;
use Rore\Framework\Type\Cast;
use Rore\Catalog\Entity\Tag;

$html     = HtmlEncoder::cast($tpl->get('html'));
$tag      = Tag::cast($tpl->get('tag'));
$products = Cast::array($tpl->tryGet('products', []));
$packs    = Cast::array($tpl->tryGet('packs', []));
// $partial is injected by the Template engine — not a param
?>
<?= $partial('partials/breadcrumb', ['breadcrumb' => [$tag]]) ?>

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
            <?= $partial('partials/product-card', ['product' => $product]) ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($packs)): ?>
<section class="mt-12">
    <h2 class="text-lg font-semibold text-gray-700 mb-4">Packs incluant ces articles</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($packs as $pack): ?>
            <?= $partial('partials/pack-card', ['pack' => $pack]) ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
