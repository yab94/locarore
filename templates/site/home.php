<!-- HERO -->
<section class="bg-brand-600 text-white py-16 rounded-2xl mb-10 text-center">
    <h1 class="text-4xl font-bold mb-3"><?= \Rore\Presentation\Template\Html::e(\Rore\Infrastructure\Config\SettingsStore::get('hero.title')) ?></h1>
    <p class="text-lg opacity-90 mb-6"><?= \Rore\Presentation\Template\Html::e(\Rore\Infrastructure\Config\SettingsStore::get('hero.subtitle')) ?></p>
    <a href="#categories" class="bg-white text-brand-700 font-semibold px-6 py-3 rounded-lg hover:bg-gray-100 transition">
        <?= \Rore\Presentation\Template\Html::e(\Rore\Infrastructure\Config\SettingsStore::get('hero.cta')) ?>
    </a>
</section>

<?php $homeIntro = \Rore\Infrastructure\Config\SettingsStore::get('home.intro'); ?>
<?php if ($homeIntro): ?>
<section class="mb-10 prose prose-sm max-w-none text-gray-700">
    <?= $homeIntro ?>
</section>
<?php endif; ?>

<!-- CATEGORIES -->
<section id="categories" class="mb-12">
    <h2 class="text-2xl font-bold text-gray-800 mb-6"><?= \Rore\Presentation\Template\Html::e(\Rore\Infrastructure\Config\SettingsStore::get('home.categories_title')) ?></h2>
    <?php if (empty($categories)): ?>
        <p class="text-gray-500">Aucune catégorie disponible pour le moment.</p>
    <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($categories as $cat): ?>
                <a href="/categorie/<?= \Rore\Presentation\Template\Html::e(\Rore\Presentation\Seo\CanonicalUrlResolver::categoryPath($cat, $allCategories)) ?>"
                   class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition text-center">
                    <h3 class="font-semibold text-gray-800"><?= \Rore\Presentation\Template\Html::e($cat->getName()) ?></h3>
                    <?php if ($cat->getDescriptionShort()): ?>
                        <p class="text-sm text-gray-500 mt-1 line-clamp-2"><?= \Rore\Presentation\Template\Html::e($cat->getDescriptionShort()) ?></p>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- PRODUITS PHARES -->
<?php if (!empty($featured)): ?>
<section>
    <h2 class="text-2xl font-bold text-gray-800 mb-6"><?= \Rore\Presentation\Template\Html::e(\Rore\Infrastructure\Config\SettingsStore::get('home.featured_title')) ?></h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($featured as $product): ?>
            <?php include BASE_PATH . '/templates/site/partials/product-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
