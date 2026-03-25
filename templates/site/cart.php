<?php
use Rore\Framework\HtmlHelper;
use Rore\Presentation\Seo\UrlResolver;
use Rore\Framework\Cast;
use Rore\Application\Settings\GetSettingUseCase;
use Rore\Framework\Config;
use Rore\Infrastructure\Session\CartSession;
use Rore\Domain\Shared\ValueObject\DateRange;

$html          = HtmlHelper::cast($tpl->get('html'));
$url           = UrlResolver::cast($tpl->get('url'));
$config        = Config::cast($tpl->get('config'));
$settings      = GetSettingUseCase::cast($tpl->get('settings'));
$urlResolver   = UrlResolver::cast($tpl->get('urlResolver'));
$allCategories = Cast::array($tpl->tryGet('allCategories', []));
$cart          = CartSession::cast($tpl->get('cart'));
$cartDateRange = DateRange::castOrNull($tpl->tryGet('cartDateRange'));
$cartProducts  = Cast::array($tpl->tryGet('cartProducts', []));
$cartPacks     = Cast::array($tpl->tryGet('cartPacks', []));
$productPrices = Cast::array($tpl->tryGet('productPrices', []));
$packPrices    = Cast::array($tpl->tryGet('packPrices', []));
// $partial is injected by the Template engine — not a param
?>
<h1 class="text-3xl font-bold text-gray-900 mb-8">Mon panier</h1>

<?php if (!$cart->hasDates()): ?>
    <!-- Étape 1 : Choisir les dates -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 max-w-lg mx-auto text-center">
        <p class="text-lg text-gray-700 mb-6">Commencez par choisir vos dates de location</p>
        <form method="post" action="<?= $url('Site\Cart.setDates') ?>" class="space-y-4">
            <?= $partial('partials/csrf') ?>
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de début</label>
                    <input type="date" name="start_date" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                           min="<?= date('Y-m-d') ?>">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin</label>
                    <input type="date" name="end_date" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                           min="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <button type="submit"
                    class="w-full bg-brand-600 text-white font-semibold py-3 rounded-xl hover:bg-brand-700 transition">
                Valider les dates
            </button>
        </form>
    </div>

<?php elseif ($cart->isEmpty()): ?>
    <!-- Panier vide (avec dates) -->
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 text-sm text-green-800">
        📅 <?= $cartDateRange->label() ?>
    </div>
    <div class="text-center py-16 text-gray-400">
        <p class="text-lg mb-4">Votre panier est vide.</p>
        <a href="/" class="text-brand-600 hover:underline">Parcourir le catalogue</a>
    </div>

<?php else: ?>
    <!-- Dates sélectionnées -->
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 flex items-center justify-between">
        <span class="text-sm text-green-800">📅 <?= $cartDateRange->label() ?></span>
        <form method="post" action="<?= $url('Site\Cart.setDates') ?>">
            <?= $partial('partials/csrf') ?>
            <input type="hidden" name="start_date" value="">
            <input type="hidden" name="end_date" value="">
            <button type="submit" class="text-xs text-red-500 hover:underline"
                    data-confirm="Modifier les dates videra votre panier. Continuer ?">
                Modifier les dates
            </button>
        </form>
    </div>
    <?php $nbJours = $cartDateRange->nbDays(); ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Liste produits + packs -->
        <div class="lg:col-span-2 space-y-4">
            <?php foreach ($cartProducts as $row): ?>
                <?php $p = $row['product']; $qty = $row['quantity']; ?>
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-4">
                    <?php if ($photo = $p->getMainPhoto()): ?>
                        <img src="<?= $html($photo->getPublicPath()) ?>" alt=""
                             width="80" height="80"
                             loading="lazy"
                             class="w-20 h-20 object-cover rounded-lg flex-shrink-0">
                    <?php else: ?>
                        <div class="w-20 h-20 bg-gray-100 rounded-lg flex-shrink-0"></div>
                    <?php endif; ?>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-800 truncate">
                            <a href="<?= $html($urlResolver->productUrl($p, $allCategories)) ?>" class="hover:text-brand-700">
                                <?= $html($p->getName()) ?>
                            </a>
                        </h3>
                        <p class="text-sm text-gray-500">Quantité : <?= $qty ?></p>
                        <p class="text-sm text-gray-500">
                            <?= number_format(($productPrices[$p->getId()] ?? 0) * $qty, 2, ',', ' ') ?> €
                        </p>
                    </div>
                    <form method="post" action="<?= $url('Site\Cart.remove') ?>">
                        <?= $partial('partials/csrf') ?>
                        <input type="hidden" name="product_id" value="<?= $p->getId() ?>">
                        <button type="submit" class="text-red-400 hover:text-red-600 text-sm transition"
                                data-confirm="Retirer ce produit du panier ?">
                            ✕
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>

            <?php foreach ($cartPacks as $packData): ?>
                <?php $pack = $packData['pack']; $slotProducts = $packData['slotProducts']; ?>
                <div class="bg-white rounded-xl border border-brand-200 p-4">
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 bg-brand-50 rounded-lg flex-shrink-0 flex items-center justify-center">
                            <span class="text-brand-600 text-xs font-semibold text-center leading-tight px-1">Pack</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-xs bg-brand-50 text-brand-700 border border-brand-200 rounded-full px-2 py-0.5 font-medium">Pack</span>
                                <h3 class="font-semibold text-gray-800 truncate">
                                    <a href="<?= $config->getString('seo.packs_base_url') ?>/<?= $html($pack->getSlug()) ?>" class="hover:text-brand-700">
                                        <?= $html($pack->getName()) ?>
                                    </a>
                                </h3>
                            </div>
                            <p class="text-sm text-gray-500">
                                <?= number_format($packPrices[$pack->getId()] ?? 0, 2, ',', ' ') ?> €
                                <span class="text-gray-400">(base <?= number_format($pack->getPricePerDay(), 0, ',', ' ') ?> €)</span>
                            </p>
                        </div>
                        <form method="post" action="<?= $url('Site\Cart.removePack') ?>">
                            <?= $partial('partials/csrf') ?>
                            <input type="hidden" name="pack_id" value="<?= $pack->getId() ?>">
                            <button type="submit" class="text-red-400 hover:text-red-600 text-sm transition"
                                    data-confirm="Retirer ce pack du panier ?">
                                ✕
                            </button>
                        </form>
                    </div>
                    <?php if (!empty($slotProducts)): ?>
                        <div class="mt-3 pl-24 border-t border-brand-100 pt-3 space-y-1">
                            <p class="text-xs text-gray-500 font-medium mb-1">Produits choisis :</p>
                            <?php foreach ($slotProducts as $slotProduct): ?>
                                <div class="flex items-center gap-2 text-sm text-gray-700">
                                    <span class="text-brand-500">›</span>
                                    <?= $html($slotProduct->getName()) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Récap -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 h-fit">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Récapitulatif</h2>
            <?php
            $total = 0;
            foreach ($cartProducts as $row) {
                $total += ($productPrices[$row['product']->getId()] ?? 0) * $row['quantity'];
            }
            foreach ($cartPacks as $packData) {
                $total += $packPrices[$packData['pack']->getId()] ?? 0;
            }
            ?>
            <div class="flex justify-between text-sm text-gray-600 mb-2">
                <span>Durée</span>
                <span><?= $nbJours ?> jour(s)</span>
            </div>
            <div class="flex justify-between text-sm text-gray-600 mb-4">
                <span>Total estimé</span>
                <span class="font-bold text-gray-900"><?= number_format($total, 2, ',', ' ') ?> €</span>
            </div>
            <a href="<?= $url('Site\Cart.checkout') ?>"
               class="block w-full bg-brand-600 text-white text-center font-semibold py-3 rounded-xl hover:bg-brand-700 transition">
                Réserver →
            </a>
            <p class="text-xs text-gray-400 mt-3 text-center">
                <?= $html($settings->get('cart.footer_note')) ?>
            </p>
        </div>
    </div>
<?php endif; ?>
