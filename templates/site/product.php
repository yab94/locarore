<!-- Fil d'ariane -->
<nav class="text-sm text-gray-500 mb-6 flex flex-wrap items-center gap-1">
    <a href="/" class="hover:underline">Accueil</a>
    <?php foreach ($breadcrumb as $crumb): ?>
        <span>›</span>
        <?php if (method_exists($crumb, 'getCategoryId')): ?>
            <!-- C'est le produit lui-même (dernier élément) -->
            <span class="text-gray-800 font-medium"><?= e($crumb->getName()) ?></span>
        <?php else: ?>
            <a href="/categorie/<?= e(categoryCanonicalPath($crumb, $allCategories)) ?>" class="hover:underline">
                <?= e($crumb->getName()) ?>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

    <!-- Carousel photos -->
    <div>
        <?php $photos = $product->getPhotos(); ?>
        <?php if (!empty($photos)): ?>
            <div id="carousel" class="relative rounded-2xl overflow-hidden bg-gray-100">
                <?php foreach ($photos as $i => $photo): ?>
                    <img data-slide="<?= $i ?>"
                         src="<?= e($photo->getPublicPath()) ?>"
                         alt="<?= e($product->getName()) ?>"
                         class="w-full h-96 object-cover <?= $i > 0 ? 'hidden' : '' ?>">
                <?php endforeach; ?>
                <?php if (count($photos) > 1): ?>
                    <button id="carousel-prev"
                            class="absolute left-2 top-1/2 -translate-y-1/2 bg-black/40 text-white rounded-full w-9 h-9 flex items-center justify-center hover:bg-black/60 transition">‹</button>
                    <button id="carousel-next"
                            class="absolute right-2 top-1/2 -translate-y-1/2 bg-black/40 text-white rounded-full w-9 h-9 flex items-center justify-center hover:bg-black/60 transition">›</button>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="rounded-2xl bg-gray-100 h-96 flex items-center justify-center text-gray-400">
                Pas de photo
            </div>
        <?php endif; ?>
    </div>

    <!-- Infos produit -->
    <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2"><?= e($product->getName()) ?></h1>
        <p class="text-2xl font-semibold text-brand-600 mb-4">
            à partir de <?= number_format($product->getPriceBase(), 0, ',', ' ') ?> €
        </p>
        <?php if ($product->getStockOnDemand() > 0): ?>
            <p class="inline-flex items-center gap-1.5 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-1.5 mb-4">
                ⚙ Fabrication à la demande disponible
            </p>
        <?php endif; ?>

        <?php if ($product->getDescription()): ?>
            <div class="text-gray-600 mb-6 leading-relaxed prose prose-sm max-w-none">
                <?= nl2br(e($product->getDescription())) ?>
            </div>
        <?php endif; ?>

        <!-- Encadré dates -->
        <?php if (!$cart || empty($cart['start_date'])): ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 mb-6">
                <p class="text-sm font-semibold text-yellow-800 mb-3">📅 Choisissez vos dates avant d'ajouter au panier</p>
                <form method="post" action="/panier/dates" class="flex flex-col sm:flex-row gap-3">
                    <input type="date" name="start_date" required
                           class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm"
                           min="<?= date('Y-m-d') ?>">
                    <input type="date" name="end_date" required
                           class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm"
                           min="<?= date('Y-m-d') ?>">
                    <input type="hidden" name="redirect" value="<?= e(productCanonicalUrl($product, $allCategories)) ?>">
                    <button type="submit"
                            class="bg-brand-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-brand-700 transition">
                        Valider
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="bg-green-50 border border-green-200 rounded-xl p-5 mb-6 text-sm text-green-800">
                <div class="flex items-center justify-between mb-3">
                    <span>📅 Du <?= htmlspecialchars($cart['start_date']) ?> au <?= htmlspecialchars($cart['end_date']) ?></span>
                    <button type="button" onclick="document.getElementById('edit-dates-form').classList.toggle('hidden')"
                            class="ml-2 text-green-600 underline text-xs">
                        Modifier
                    </button>
                </div>
                <form id="edit-dates-form" method="post" action="/panier/dates"
                      class="hidden flex-col sm:flex-row gap-3 flex">
                    <input type="date" name="start_date" required
                           value="<?= htmlspecialchars($cart['start_date']) ?>"
                           class="flex-1 border border-green-300 rounded-lg px-3 py-1.5 text-sm bg-white text-gray-800"
                           min="<?= date('Y-m-d') ?>">
                    <input type="date" name="end_date" required
                           value="<?= htmlspecialchars($cart['end_date']) ?>"
                           class="flex-1 border border-green-300 rounded-lg px-3 py-1.5 text-sm bg-white text-gray-800"
                           min="<?= date('Y-m-d') ?>">
                    <input type="hidden" name="redirect" value="<?= e(productCanonicalUrl($product, $allCategories)) ?>">
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
                <form method="post" action="/panier/ajouter">
                    <input type="hidden" name="product_id" value="<?= $product->getId() ?>">
                    <input type="hidden" name="redirect" value="<?= e(productCanonicalUrl($product, $allCategories)) ?>">
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

<?php if (count($product->getPhotos() ?: []) > 1): ?>
<script>
(function () {
    const slides = document.querySelectorAll('#carousel [data-slide]');
    let current = 0;
    function go(n) {
        slides[current].classList.add('hidden');
        current = (n + slides.length) % slides.length;
        slides[current].classList.remove('hidden');
    }
    document.getElementById('carousel-prev').addEventListener('click', () => go(current - 1));
    document.getElementById('carousel-next').addEventListener('click', () => go(current + 1));
})();
</script>
<?php endif; ?>

