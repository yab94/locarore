<?php
use Rore\Domain\Catalog\Entity\Pack;
use Rore\Framework\HtmlHelper;
use Rore\Framework\Cast;
use Rore\Framework\Config;

$pack          = Pack::cast($tpl->get('pack'));
$productsById  = Cast::array($tpl->get('productsById'));
$config        = Config::cast($tpl->get('config'));
$html          = HtmlHelper::cast($tpl->get('html'));
// $productsById : array<int, Product> (tous les produits du pack indexés par id)
// Détermine la photo via le produit principal
$_mainProductId = $pack->getMainProductId($productsById);
$_mainProduct   = $_mainProductId ? ($productsById[$_mainProductId] ?? null) : null;
$_mainPhoto     = $_mainProduct?->getMainPhoto();
$_packUrl       = $config->getString('seo.packs_base_url') . '/' . $pack->getSlug();
?>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
    <?php if ($_mainPhoto): ?>
        <?php $_alt = $html($_mainPhoto->getDescription() ?: $pack->getName()); ?>
        <img src="<?= $html($_mainPhoto->getPublicPath()) ?>"
             alt="<?= $_alt ?>"
             title="<?= $_alt ?>"
             width="400" height="192"
             loading="lazy"
             class="w-full h-48 object-cover">
    <?php else: ?>
        <div class="w-full h-48 bg-gray-100 flex items-center justify-center text-gray-400 text-sm">
            Pas de photo
        </div>
    <?php endif; ?>
    <div class="p-4">
        <div class="inline-flex items-center gap-1 bg-brand-50 text-brand-700 border border-brand-200 rounded-full px-2 py-0.5 text-xs font-medium mb-2">Pack</div>
        <h3 class="font-semibold text-gray-900 mb-1" style="font-family:'Roboto Slab',serif"><?= $html($pack->getName()) ?></h3>
        <p class="text-sm text-gray-500 mb-3 line-clamp-2"><?= $html($pack->getDescriptionShort() ?? strip_tags($pack->getDescription() ?? '')) ?></p>
        <div class="flex items-center justify-between">
            <span class="text-brand-600 font-bold text-sm">
                <?= number_format($pack->getPricePerDay(), 0, ',', ' ') ?> € / jour
            </span>
            <a href="<?= $html($_packUrl) ?>"
               class="text-sm bg-brand-600 text-white px-3 py-1.5 rounded-full hover:bg-brand-700 transition font-medium">
                Voir
            </a>
        </div>
    </div>
</div>
