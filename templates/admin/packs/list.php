<?php
use RRB\View\HtmlEncoder;
use RRB\Http\UrlResolver;
use RRB\Type\Cast;
use RRB\Bootstrap\Config;

$html       = HtmlEncoder::cast($tpl->get('html'));
$url        = UrlResolver::cast($tpl->get('url'));
$config     = Config::cast($tpl->get('config'));
$packs      = Cast::array($tpl->tryGet('packs', []));
$products   = Cast::array($tpl->tryGet('products', []));
$categories = Cast::array($tpl->tryGet('categories', []));
// $partial is injected by the Template engine — not a param
?>
<div class="flex justify-between items-center mb-6">
    <p class="text-sm text-gray-500"><?= count($packs) ?> pack(s)</p>
    <a href="<?= $url('Admin\Pack.create') ?>"
       class="bg-brand-600 text-white text-sm font-semibold px-4 py-2.5 rounded-lg hover:bg-brand-700 transition">
        + Nouveau pack
    </a>
</div>

<?php if (empty($packs)): ?>
    <div class="text-center py-16 text-gray-400">
        <p class="text-5xl mb-4">🎁</p>
        <p>Aucun pack créé pour l'instant.</p>
        <a href="<?= $url('Admin\Pack.create') ?>" class="mt-4 inline-block text-brand-600 hover:underline text-sm">
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
                    <th class="px-6 py-3 text-right">Prix pack</th>
                    <th class="px-6 py-3 text-right">Prix détail</th>
                    <th class="px-6 py-3 text-center">Statut</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($packs as $pack): ?>
                <?php
                $hasZeroStock = false;
                foreach ($pack->getItems() as $item) {
                    if (!$item->isFixed()) {
                        continue;
                    }
                    $fixedProduct = $products[$item->getProductId()] ?? null;
                    if ($fixedProduct !== null && $fixedProduct->getTotalStock() <= 0) {
                        $hasZeroStock = true;
                        break;
                    }
                }
                ?>
                <tr class="<?= $hasZeroStock ? 'bg-red-50/70 hover:bg-red-50' : 'hover:bg-gray-50' ?> transition">
                    <td class="px-6 py-4 font-medium text-gray-800">
                        <?= $html($pack->getName()) ?>
                        <?php if ($hasZeroStock): ?>
                            <span class="ml-2 inline-flex items-center gap-1 rounded-full bg-red-100 text-red-700 px-2 py-0.5 text-xs font-semibold ring-1 ring-red-200">
                                ⚠ Stock 0
                            </span>
                        <?php endif; ?>
                        <div class="text-xs text-gray-400 font-normal"><?= $config->getString('seo.products_base_url'); ?>/<?= $html($pack->getSlug()) ?></div>
                    </td>
                    <td class="px-6 py-4 text-gray-500">
                        <?php $items = $pack->getItems(); ?>
                        <?php if (empty($items)): ?>
                            <span class="text-gray-300">—</span>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <?php
                                $itemOutOfStock = false;
                                if ($item->isFixed()) {
                                    $fixedProduct = $products[$item->getProductId()] ?? null;
                                    $itemOutOfStock = $fixedProduct !== null && $fixedProduct->getTotalStock() <= 0;
                                }
                                ?>
                                <span class="inline-flex items-center gap-1 rounded px-2 py-0.5 text-xs mr-1 mb-1 <?= $itemOutOfStock ? 'bg-red-100 text-red-700 ring-1 ring-red-200 font-semibold' : 'bg-gray-100' ?>">
                                    <?php if ($item->isFixed()): ?>
                                        <?= $html($products[$item->getProductId()]?->getName() ?? 'Produit #' . $item->getProductId()) ?>
                                        <?php if ($itemOutOfStock): ?>
                                            <span title="Stock nul">⚠</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        Produit &quot;<?= $html($categories[$item->getCategoryId()]?->getName()) ?>&quot;</em>
                                    <?php endif; ?>
                                    × <?= $item->getQuantity() ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-right font-semibold text-brand-600">
                        <?= number_format($pack->getPricePerDay(), 2, ',', ' ') ?> €
                    </td>
                    <td class="px-6 py-4 text-right">
                        <?php
                        $hasSlots = array_filter($pack->getItems(), fn($i) => $i->isSlot()) !== [];
                        if ($hasSlots): ?>
                            <span class="text-xs text-gray-400 italic">variable</span>
                        <?php else:
                        $retailPrice = $packRetailPrices[$pack->getId()] ?? 0;
                        $packPrice = $pack->getPricePerDay();
                        $diff = $retailPrice > 0 ? (($packPrice - $retailPrice) / $retailPrice) * 100 : 0;
                        $diffSign = $diff > 0 ? '+' : '';
                        $diffColor = $diff < 0 ? 'text-green-600' : ($diff > 0 ? 'text-red-600' : 'text-gray-500');
                        ?>
                        <div class="text-gray-600"><?= number_format($retailPrice, 2, ',', ' ') ?> €</div>
                        <?php if ($retailPrice > 0): ?>
                            <div class="text-xs <?= $diffColor ?> font-medium">
                                <?= $diffSign ?><?= number_format($diff, 0) ?>%
                            </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <form method="post" action="<?= $url('Admin\Pack.toggle', ['id' => $pack->getId()]) ?>" class="inline">
                            <?= $partial('partials/csrf') ?>
                            <button type="submit"
                                    class="inline-block px-2 py-0.5 rounded-full text-xs font-medium <?= $pack->isActive() ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' ?> transition">
                                <?= $pack->isActive() ? 'Actif' : 'Inactif' ?>
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="<?= $url('Admin\Pack.edit', ['id' => $pack->getId()]) ?>"
                           class="text-brand-600 hover:underline text-sm">Modifier</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
