<?php
use Rore\Framework\View\HtmlHelper;
use Rore\Framework\Http\UrlResolver;
use Rore\Framework\Support\Cast;
use Rore\Domain\Catalog\Entity\Pack;
use Rore\Domain\Catalog\Entity\Product;
use Rore\Domain\Cart\ValueObject\CartState;

$html            = HtmlHelper::cast($tpl->get('html'));
$url             = UrlResolver::cast($tpl->get('url'));
$pack            = Pack::cast($tpl->get('pack'));
$allCategories   = Cast::array($tpl->tryGet('allCategories', []));
$productsById    = Cast::array($tpl->tryGet('productsById', []));
$mainProduct     = Product::castOrNull($tpl->tryGet('mainProduct'));
$slotsWithProducts = Cast::array($tpl->tryGet('slotsWithProducts', []));
$packSelections  = Cast::array($tpl->tryGet('packSelections', []));
$cart            = CartState::cast($tpl->get('cart'));
// $partial is injected by the Template engine — not a param
?>
<?= $partial('partials/breadcrumb') ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

    <!-- Photo principale (produit principal du pack) -->
    <div>
        <?php $mainPhoto = $mainProduct?->getMainPhoto(); ?>
        <?php if ($mainPhoto): ?>
            <?php $photoAlt = $html($mainPhoto->getDescription() ?: $pack->getName()); ?>
            <div class="rounded-2xl overflow-hidden bg-gray-100">
                <img src="<?= $html($mainPhoto->getPublicPath()) ?>"
                     alt="<?= $photoAlt ?>"
                     title="<?= $photoAlt ?>"
                     width="768" height="384"
                     fetchpriority="high"
                     class="w-full h-96 object-cover">
            </div>
        <?php else: ?>
            <div class="rounded-2xl bg-gray-100 h-96 flex items-center justify-center text-gray-400">
                Pas de photo
            </div>
        <?php endif; ?>
    </div>

    <!-- Infos pack -->
    <div>
        <div class="inline-flex items-center gap-1.5 bg-brand-50 text-brand-700 border border-brand-200 rounded-full px-3 py-1 text-xs font-medium mb-3">
            Pack
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2"><?= $html($pack->getName()) ?></h1>
        <p class="text-2xl font-semibold text-brand-600 mb-4">
            <?= number_format($pack->getPricePerDay(), 0, ',', ' ') ?> € / jour
        </p>

        <?php if ($pack->getDescription()): ?>
            <div class="text-gray-600 mb-6 leading-relaxed prose prose-sm max-w-none">
                <?= $pack->getDescription() ?>
            </div>
        <?php endif; ?>

        <!-- Contenu du pack -->
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wide">Contenu du pack</h2>
            <ul class="space-y-2">
                <?php foreach ($pack->getItems() as $item): ?>
                    <?php if ($item->isFixed()): ?>
                        <?php $p = $productsById[$item->getProductId()] ?? null; ?>
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-gray-800">
                                <?php if ($p): ?>
                                    <a href="<?= $html($slug->productUrl($p, $allCategories)) ?>"
                                       class="hover:text-brand-600 hover:underline">
                                        <?= $html($p->getName()) ?>
                                    </a>
                                <?php else: ?>
                                    Produit #<?= $item->getProductId() ?>
                                <?php endif; ?>
                            </span>
                            <span class="text-gray-500 font-medium">× <?= $item->getQuantity() ?></span>
                        </li>
                    <?php else: ?>
                        <?php
                        $slotData        = $slotsWithProducts[$item->getId()] ?? null;
                        $selectedProduct = $packSelections[$item->getId()] ?? null;
                        $selectedProduct = $selectedProduct ? ($productsById[$selectedProduct] ?? null) : null;
                        ?>
                        <li class="text-sm border-t border-dashed border-gray-200 pt-2 mt-1">
                            <span class="text-gray-500 italic">
                                <?= $item->getQuantity() ?> produit(s) au choix
                                <?php if ($slotData && $slotData['category']): ?>
                                    — <span class="text-brand-600"><?= $html($slotData['category']->getName()) ?></span>
                                <?php endif; ?>
                            </span>
                            <?php if ($selectedProduct): ?>
                                <div class="mt-1 flex items-center justify-between">
                                    <span class="text-gray-800 font-medium">→ <?= $html($selectedProduct->getName()) ?></span>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- CTA ajout panier -->
        <?php if ($cart->hasDates()): ?>
            <?php if (isset($cart->getPacks()[$pack->getId()])): ?>
                <div class="flex items-center gap-3">
                    <span class="flex-1 block text-center bg-green-100 text-green-800 font-semibold py-3 rounded-xl border border-green-200">
                        ✓ Pack dans le panier
                    </span>
                    <form method="post" action="<?= $html($url('Site\Cart.removePack')) ?>">
                        <?= $partial('partials/csrf') ?>
                        <input type="hidden" name="pack_id" value="<?= $pack->getId() ?>">
                        <button type="submit"
                                class="text-red-400 hover:text-red-600 text-sm transition py-3 px-2"
                                data-confirm="Retirer ce pack du panier ?">✕</button>
                    </form>
                </div>
            <?php else: ?>
                <form method="post" action="<?= $html($url('Site\Cart.addPack')) ?>">
                    <?= $partial('partials/csrf') ?>
                    <input type="hidden" name="pack_id" value="<?= $pack->getId() ?>">
                    <?php foreach ($slotsWithProducts as $slotId => $slotData): ?>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Choisir <?= $slotData['slot']->getQuantity() ?> produit(s)
                            <?php if ($slotData['category']): ?>
                                — <span class="text-brand-600"><?= $html($slotData['category']->getName()) ?></span>
                            <?php endif; ?>
                        </label>
                        <select name="slot_selection[<?= $slotId ?>]"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600"
                                required>
                            <option value="">— Choisir un produit —</option>
                            <?php foreach ($slotData['products'] as $p): ?>
                                <option value="<?= $p->getId() ?>"
                                    <?= ($packSelections[$slotId] ?? null) == $p->getId() ? 'selected' : '' ?>>
                                    <?= $html($p->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endforeach; ?>
                    <button type="submit"
                            class="block w-full text-center bg-brand-600 text-white font-semibold py-3 rounded-xl hover:bg-brand-700 transition">
                        Ajouter ce pack au panier
                    </button>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <a href="<?= $html($url('Site\Cart.index')) ?>"
               class="block w-full text-center bg-brand-600 text-white font-semibold py-3 rounded-xl hover:bg-brand-700 transition">
                Choisir mes dates pour réserver
            </a>
        <?php endif; ?>
        <p class="text-xs text-gray-400 mt-2 text-center">Réservation confirmée après validation de votre devis.</p>
    </div>
</div>

<?php
echo $partial('partials/breadcrumb-ld-json', [
    'item' => $pack,
    'type' => 'pack',
    'mainPhoto' => $mainProduct?->getMainPhoto()
]);
?>
