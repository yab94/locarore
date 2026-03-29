<?php
use RRB\View\HtmlEncoder;
use RRB\Http\UrlResolver;
use RRB\Type\Cast;

$html       = HtmlEncoder::cast($tpl->get('html'));
$url        = UrlResolver::cast($tpl->get('url'));
$categories = Cast::array($tpl->tryGet('categories', []));
// $partial is injected by the Template engine — not a param
?>
<div class="flex items-center justify-between mb-6">
    <div></div>
    <a href="<?= $url('Admin\\Category.create') ?>"
       class="bg-brand-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-brand-700 transition">
        + Nouvelle catégorie
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
                <th class="px-6 py-3 text-left">Nom</th>
                <th class="px-6 py-3 text-left">Slug</th>
                <th class="px-6 py-3 text-center">Produits</th>
                <th class="px-6 py-3 text-center">Statut</th>
                <th class="px-6 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($categories as $cat): ?>
                <?php $isEmpty = $cat->isActive() && $cat->getProductCount() === 0; ?>
                <tr class="<?= $isEmpty ? 'bg-red-50/70 hover:bg-red-50' : 'hover:bg-gray-50' ?>">
                    <td class="px-6 py-4 font-medium text-gray-800">
                        <?= $html($cat->getName()) ?>
                        <?php if ($isEmpty): ?>
                            <span class="ml-2 inline-block px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">0 produit</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-gray-400 font-mono text-xs"><?= $html($cat->getSlug()) ?></td>
                    <td class="px-6 py-4 text-center font-medium <?= $isEmpty ? 'text-red-600' : 'text-gray-700' ?>"><?= $cat->getProductCount() ?></td>
                    <td class="px-6 py-4 text-center">
                        <form method="post" action="<?= $url('Admin\\Category.toggle', ['id' => $cat->getId()]) ?>" class="inline">
                            <?= $partial('partials/csrf') ?>
                            <button type="submit"
                                    class="inline-block px-2 py-0.5 rounded-full text-xs font-medium <?= $cat->isActive() ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' ?> transition">
                                <?= $cat->isActive() ? 'Actif' : 'Inactif' ?>
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="<?= $url('Admin\\Category.edit', ['id' => $cat->getId()]) ?>"
                           class="text-brand-600 hover:underline text-sm">Modifier</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
