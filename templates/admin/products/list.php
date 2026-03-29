<?php
use RRB\View\HtmlEncoder;
use RRB\Http\UrlResolver;
use RRB\Type\Cast;

$html           = HtmlEncoder::cast($tpl->get('html'));
$url            = UrlResolver::cast($tpl->get('url'));
$products       = Cast::array($tpl->tryGet('products', []));
$categoriesById = Cast::array($tpl->tryGet('categoriesById', []));
// $partial is injected by the Template engine — not a param
?>
<div class="flex items-center justify-between mb-6">
    <div></div>
    <a href="<?= $url('Admin\\Product.create') ?>"
       class="bg-brand-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-brand-700 transition">
        + Nouveau produit
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
                <th class="px-6 py-3 text-left">Nom</th>
                <th class="px-6 py-3 text-left">Catégorie</th>
                <th class="px-6 py-3 text-right">Stock</th>
                <th class="px-6 py-3 text-right">Prix/j</th>
                <th class="px-6 py-3 text-center">Statut</th>
                <th class="px-6 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($products as $p): ?>
                <?php $isOutOfStock = $p->isActive() && $p->getTotalStock() <= 0; ?>
                <tr class="<?= $isOutOfStock ? 'bg-red-50/70 hover:bg-red-50' : 'hover:bg-gray-50' ?>">
                    <td class="px-6 py-4 font-medium text-gray-800">
                        <div class="flex items-center gap-3">
                            <?php if ($photo = $p->getMainPhoto()): ?>
                                <img src="<?= $html($photo->getPublicPath()) ?>" class="w-10 h-10 object-cover rounded">
                            <?php else: ?>
                                <div class="w-10 h-10 bg-gray-100 rounded"></div>
                            <?php endif; ?>
                            <span><?= $html($p->getName()) ?></span>
                            <?php if ($isOutOfStock): ?>
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 text-red-700 px-2 py-0.5 text-xs font-semibold ring-1 ring-red-200">
                                    ⚠ Stock 0
                                </span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-500">
                        <?= $html(($categoriesById[$p->getCategoryId()] ?? null)?->getName() ?? '—') ?>
                    </td>
                    <td class="px-6 py-4 text-right <?= $isOutOfStock ? 'text-red-700 font-semibold' : '' ?>">
                        <?= $p->getStock() ?>
                        <?php if ($p->getStockOnDemand() > 0): ?>
                            <span class="text-xs text-amber-600 font-medium">
                                +<?= $p->getStockOnDemand() ?> ⚒
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-right"><?= number_format($p->getPriceBase(), 2, ',', ' ') ?> €</td>
                    <td class="px-6 py-4 text-center">
                        <form method="post" action="<?= $url('Admin\Product.toggle', ['id' => $p->getId()]) ?>" class="inline">
                            <?= $partial('partials/csrf') ?>
                            <button type="submit"
                                    class="inline-block px-2 py-0.5 rounded-full text-xs font-medium <?= $p->isActive() ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' ?> transition">
                                <?= $p->isActive() ? 'Actif' : 'Inactif' ?>
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="<?= $url('Admin\Product.edit', ['id' => $p->getId()]) ?>"
                           class="text-brand-600 hover:underline">Modifier</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
