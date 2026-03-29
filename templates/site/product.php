<?php
use RRB\View\HtmlEncoder;
use RRB\Http\UrlResolver;
use RRB\Type\Cast;
use Rore\Domain\Catalog\Entity\Product;
use Rore\Domain\Cart\ValueObject\CartState;

$html          = HtmlEncoder::cast($tpl->get('html'));
$url           = UrlResolver::cast($tpl->get('url'));
$product       = Product::cast($tpl->get('product'));
$allCategories = Cast::array($tpl->tryGet('allCategories', []));
$cart          = CartState::cast($tpl->get('cart'));
$availableQty  = Cast::int($tpl->tryGet('availableQty', 0));
// $partial is injected by the Template engine — not a param
?>
<?= $partial('partials/breadcrumb') ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

    <!-- Carousel photos -->
    <div>
        <?php
        $carouselPhotos = array_map(
            fn($ph) => ['photo' => $ph, 'label' => $product->getName()],
            $product->getPhotos()
        );
        ?>
        <?= $partial('partials/carousel', [
            'carouselId'     => 'carousel-product-' . $product->getId(),
            'carouselPhotos' => $carouselPhotos,
        ]) ?>
    </div>

    <!-- Infos produit -->
    <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2"><?= $html($product->getName()) ?></h1>
        <p class="text-2xl font-semibold text-brand-600 mb-4">
            à partir de <?= number_format($product->getPriceBase(), 0, ',', ' ') ?> €
        </p>
        <?php if ($product->getDescription()): ?>
            <div class="text-gray-600 mb-4 leading-relaxed prose prose-sm max-w-none">
                <?= $product->getDescription() ?>
            </div>
        <?php endif; ?>

        <!-- Tags -->
        <?= $partial('partials/tag-list', ['tags' => $product->getTags()]) ?>

        <!-- Encadré dates -->
        <?php if (!$cart->hasDates()): ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 mb-6">
                <p class="text-sm font-semibold text-yellow-800 mb-3">📅 Choisissez vos dates avant d'ajouter au panier</p>
                <form method="post" action="<?= $url('Site\Cart.setDates') ?>" class="flex flex-col sm:flex-row gap-3">
                    <?= $partial('partials/csrf') ?>
                    <input type="date" name="start_date" required
                           class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm"
                           min="<?= date('Y-m-d') ?>">
                    <input type="date" name="end_date" required
                           class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm"
                           min="<?= date('Y-m-d') ?>">
                    <input type="hidden" name="redirect" value="<?= $html($slug->productUrl($product, $allCategories)) ?>">
                    <button type="submit"
                            class="bg-brand-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-brand-700 transition">
                        Valider
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="bg-green-50 border border-green-200 rounded-xl p-5 mb-6 text-sm text-green-800">
                <div class="flex items-center justify-between mb-3">
                    <span>📅 Du <?= htmlspecialchars($cart->getStartDate()->format('d/m/Y')) ?> au <?= htmlspecialchars($cart->getEndDate()->format('d/m/Y')) ?></span>
                    <button type="button" onclick="document.getElementById('edit-dates-form').classList.toggle('hidden')"
                            class="ml-2 text-green-600 underline text-xs">
                        Modifier
                    </button>
                </div>
                <form id="edit-dates-form" method="post" action="<?= $url('Site\Cart.setDates') ?>"
                      class="hidden flex-col sm:flex-row gap-3 flex">
                    <?= $partial('partials/csrf') ?>
                    <input type="date" name="start_date" required
                           value="<?= htmlspecialchars($cart->getStartDate()->format('Y-m-d')) ?>"
                           class="flex-1 border border-green-300 rounded-lg px-3 py-1.5 text-sm bg-white text-gray-800"
                           min="<?= date('Y-m-d') ?>">
                    <input type="date" name="end_date" required
                           value="<?= htmlspecialchars($cart->getEndDate()->format('Y-m-d')) ?>"
                           class="flex-1 border border-green-300 rounded-lg px-3 py-1.5 text-sm bg-white text-gray-800"
                           min="<?= date('Y-m-d') ?>">
                    <input type="hidden" name="redirect" value="<?= $html($slug->productUrl($product, $allCategories)) ?>">
                    <button type="submit"
                            class="bg-green-700 text-white px-4 py-1.5 rounded-lg text-sm hover:bg-green-800 transition">
                        Valider
                    </button>
                </form>
            </div>

            <?php if ($availableQty !== null && $availableQty <= 0): ?>
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 text-sm text-red-800">
                    Stock épuisé sur cette période.
                </div>
            <?php else: ?>
                <form method="post" action="<?= $url('Site\Cart.add') ?>">
                    <?= $partial('partials/csrf') ?>
                    <input type="hidden" name="product_id" value="<?= $product->getId() ?>">
                    <input type="hidden" name="redirect" value="<?= $html($slug->productUrl($product, $allCategories)) ?>">
                    <div class="flex items-center gap-3 mb-4">
                        <label class="text-sm font-medium text-gray-700">Quantité</label>
                        <input type="number" name="quantity" value="1" min="1"
                               max="<?= $availableQty ?? $product->getStock() ?>"
                               class="w-20 border border-gray-300 rounded-lg px-3 py-2 text-sm text-center">
                        <?php if ($availableQty !== null): ?>
                            <span class="text-sm text-gray-500">/ <?= $availableQty ?> disponible(s)</span>
                        <?php endif; ?>
                    </div>
                    <button type="submit"
                            class="w-full bg-brand-600 text-white font-semibold py-3 rounded-xl hover:bg-brand-700 transition">
                        Ajouter au panier
                    </button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php
echo $partial('partials/breadcrumb-ld-json', [
    'item' => $product,
    'type' => 'product'
]);
?>
