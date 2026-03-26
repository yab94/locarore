<?php
use Rore\Domain\Catalog\Entity\Product;
use Rore\Framework\HtmlHelper;
use Rore\Framework\UrlResolver;
use Rore\Framework\Cast;
use Rore\Framework\Config;

$product            = Product::cast($tpl->get('product'));
$html               = HtmlHelper::cast($tpl->get('html'));
$config             = Config::cast($tpl->get('config'));
$allCategories      = Cast::array($tpl->tryGet('allCategories', []));
/** @var string|null $productContextPath */
$productContextPath = $tpl->tryGet('productContextPath', null);
$_productUrl = $productContextPath !== null
    ? $config->getString('seo.products_base_url') . '/' . $productContextPath . '/' . $product->getSlug()
    : $slug->productUrl($product, $allCategories);
?>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
    <?php if ($photo = $product->getMainPhoto()): ?>
        <?php $photoAlt = $html($photo->getDescription() ?: $product->getName()); ?>
        <img src="<?= $html($photo->getPublicPath()) ?>"
             alt="<?= $photoAlt ?>"
             title="<?= $photoAlt ?>"
             width="400" height="192"
             loading="lazy"
             class="w-full h-48 object-cover">
    <?php else: ?>
        <div class="w-full h-48 bg-gray-100 flex items-center justify-center text-gray-400 text-sm">
            Pas de photo
        </div>
    <?php endif; ?>
    <div class="p-4">
        <h3 class="font-semibold text-gray-900 mb-1" style="font-family:'Roboto Slab',serif"><?= $html($product->getName()) ?></h3>
        <p class="text-sm text-gray-500 mb-3 line-clamp-2"><?= $html($product->getDescriptionShort() ?? strip_tags($product->getDescription() ?? '')) ?></p>
        <div class="flex items-center justify-between">
            <span class="text-brand-600 font-bold text-sm">
                à partir de <?= number_format($product->getPriceBase(), 0, ',', ' ') ?> €
            </span>
            <a href="<?= $html($_productUrl) ?>"
               class="text-sm bg-brand-600 text-white px-3 py-1.5 rounded-full hover:bg-brand-700 transition font-medium">
                Voir
            </a>
        </div>
    </div>
</div>
