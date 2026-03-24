<div class="flex justify-between items-center mb-6">
    <p class="text-sm text-gray-500"><?= count($packs) ?> pack(s)</p>
    <a href="<?= $urlResolver->resolve(\Rore\Presentation\Controller\Admin\PackController::class . '.create') ?>"
       class="bg-brand-600 text-white text-sm font-semibold px-4 py-2.5 rounded-lg hover:bg-brand-700 transition">
        + Nouveau pack
    </a>
</div>

<?php if (empty($packs)): ?>
    <div class="text-center py-16 text-gray-400">
        <p class="text-5xl mb-4">🎁</p>
        <p>Aucun pack créé pour l'instant.</p>
        <a href="<?= $urlResolver->resolve(\Rore\Presentation\Controller\Admin\PackController::class . '.create') ?>" class="mt-4 inline-block text-brand-600 hover:underline text-sm">
            Créer le premier pack
        </a>
    </div>
<?php else: ?>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="text-xs uppercase text-gray-500 bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left">Nom</th>
                    <th class="px-6 py-3 text-left">Composition</th>
                    <th class="px-6 py-3 text-right">Prix/jour</th>
                    <th class="px-6 py-3 text-center">Statut</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($packs as $pack): ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-medium text-gray-800">
                        <?= \Rore\Presentation\Template\Html::e($pack->getName()) ?>
                        <div class="text-xs text-gray-400 font-normal"><?= $config->getStringParam('seo.products_base_url'); ?>/<?= \Rore\Presentation\Template\Html::e($pack->getSlug()) ?></div>
                    </td>
                    <td class="px-6 py-4 text-gray-500">
                        <?php $items = $pack->getItems(); ?>
                        <?php if (empty($items)): ?>
                            <span class="text-gray-300">—</span>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <span class="inline-flex items-center gap-1 bg-gray-100 rounded px-2 py-0.5 text-xs mr-1 mb-1">
                                    <?= \Rore\Presentation\Template\Html::e($products[$item->getProductId()]?->getName() ?? 'Produit #' . $item->getProductId()) ?>
                                    × <?= $item->getQuantity() ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-right font-semibold text-brand-600">
                        <?= number_format($pack->getPricePerDay(), 2, ',', ' ') ?> €
                    </td>
                    <td class="px-6 py-4 text-center">
                        <form method="post" action="<?= $urlResolver->resolve(\Rore\Presentation\Controller\Admin\PackController::class . '.toggle', ['id' => $pack->getId()]) ?>" class="inline">
                            <?= require BASE_PATH . '/templates/partials/csrf.php' ?>
                            <button type="submit"
                                    class="inline-block px-2 py-0.5 rounded-full text-xs font-medium <?= $pack->isActive() ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' ?> transition">
                                <?= $pack->isActive() ? 'Actif' : 'Inactif' ?>
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="<?= $urlResolver->resolve(\Rore\Presentation\Controller\Admin\PackController::class . '.edit', ['id' => $pack->getId()]) ?>"
                           class="text-brand-600 hover:underline text-sm">Modifier</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
