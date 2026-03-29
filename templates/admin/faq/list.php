<?php
use RRB\View\HtmlEncoder;
use RRB\Http\UrlResolver;
use RRB\Type\Cast;

$html  = HtmlEncoder::cast($tpl->get('html'));
$url   = UrlResolver::cast($tpl->get('url'));
$items = Cast::array($tpl->tryGet('items', []));
// $partial is injected by the Template engine — not a param
?>
<div class="flex items-center justify-between mb-6">
    <div></div>
    <a href="<?= $url('Admin\\Faq.create') ?>"
       class="bg-brand-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-brand-700 transition">
        + Nouvelle question
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
                <th class="px-6 py-3 text-left w-8">Ordre</th>
                <th class="px-6 py-3 text-left">Question</th>
                <th class="px-6 py-3 text-center">Statut</th>
                <th class="px-6 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($items as $item): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-gray-400 font-mono text-xs text-center"><?= $item->getPosition() ?></td>
                    <td class="px-6 py-4 font-medium text-gray-800"><?= $html($item->getQuestion()) ?></td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium
                            <?= $item->isVisible() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' ?>">
                            <?= $item->isVisible() ? 'Visible' : 'Masqué' ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-3">
                        <a href="<?= $url('Admin\\Faq.edit', ['id' => $item->getId()]) ?>"
                           class="text-brand-600 hover:underline text-sm">Modifier</a>
                        <form method="post" action="<?= $url('Admin\\Faq.toggle', ['id' => $item->getId()]) ?>"
                              class="inline">
                            <?= $partial('partials/csrf') ?>
                            <button type="submit" class="text-gray-500 hover:text-gray-800 text-sm transition">
                                <?= $item->isVisible() ? 'Masquer' : 'Afficher' ?>
                            </button>
                        </form>
                        <form method="post" action="<?= $url('Admin\\Faq.delete', ['id' => $item->getId()]) ?>"
                              class="inline"
                              onsubmit="return confirm('Supprimer cette question ?')">
                            <?= $partial('partials/csrf') ?>
                            <button type="submit" class="text-red-500 hover:text-red-700 text-sm transition">
                                Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-400 text-sm">
                        Aucune question pour le moment.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
