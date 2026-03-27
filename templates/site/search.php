<?php
use Rore\Framework\Type\Cast;
use Rore\Framework\View\HtmlHelper;
use Rore\Framework\Http\UrlResolver;

$html        = HtmlHelper::cast($tpl->get('html'));
$query       = Cast::string($tpl->tryGet('query', ''));
$products    = Cast::array($tpl->get('products'));
$packs       = Cast::array($tpl->get('packs'));
$total       = count($products) + count($packs);
?>
<div class="max-w-5xl mx-auto">

    <!-- Formulaire de recherche -->
    <form method="get" action="/recherche" class="mb-8 flex items-center max-w-xl">
        <input
            type="search"
            name="q"
            value="<?= $html($query) ?>"
            placeholder="Rechercher un produit, une catégorie, un tag…"
            autofocus
            class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        <button type="submit" class="ml-2 text-gray-700 hover:text-brand-700 transition-colors" title="Rechercher">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
            </svg>
        </button>
    </form>

    <?php if ($query === ''): ?>
        <p class="text-gray-500 text-sm text-center py-12">Saisissez un mot-clé pour lancer la recherche.</p>

    <?php elseif ($total === 0): ?>
        <p class="text-gray-600 text-center py-12">
            Aucun résultat pour <strong>« <?= $html($query) ?> »</strong>.
        </p>

    <?php else: ?>

        <p class="text-sm text-gray-500 mb-6">
            <?= $total ?> résultat<?= $total > 1 ? 's' : '' ?> pour
            <strong class="text-gray-800">« <?= $html($query) ?> »</strong>
        </p>

        <!-- Produits -->
        <?php if (!empty($products)): ?>
        <section class="mb-10">
            <h2 class="text-lg font-semibold text-gray-800 mb-4" style="font-family:'Roboto Slab',serif">
                Produits
                <span class="text-sm font-normal text-gray-400 ml-1">(<?= count($products) ?>)</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($products as $product): ?>
                    <?= $partial('partials/product-card', ['product' => $product]) ?>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Packs -->
        <?php if (!empty($packs)): ?>
        <section>
            <h2 class="text-lg font-semibold text-gray-800 mb-4" style="font-family:'Roboto Slab',serif">
                Packs
                <span class="text-sm font-normal text-gray-400 ml-1">(<?= count($packs) ?>)</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($packs as $pack): ?>
                    <?= $partial('partials/pack-card', ['pack' => $pack, 'productsById' => $productsById]) ?>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

    <?php endif; ?>

</div>