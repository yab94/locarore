<!-- HERO -->
<section class="bg-brand-600 text-white py-16 rounded-2xl mb-10 text-center">
    <h1 class="text-4xl font-bold mb-3"><?= se('hero.title') ?></h1>
    <p class="text-lg opacity-90 mb-6"><?= se('hero.subtitle') ?></p>
    <a href="#categories" class="bg-white text-brand-700 font-semibold px-6 py-3 rounded-lg hover:bg-gray-100 transition">
        <?= se('hero.cta') ?>
    </a>
</section>

<?php $homeIntro = setting('home.intro'); ?>
<?php if ($homeIntro): ?>
<section class="mb-10 prose prose-sm max-w-none text-gray-700">
    <?= nl2br(e($homeIntro)) ?>
</section>
<?php endif; ?>

<!-- CATEGORIES -->
<section id="categories" class="mb-12">
    <h2 class="text-2xl font-bold text-gray-800 mb-6"><?= se('home.categories_title') ?></h2>
    <?php if (empty($categories)): ?>
        <p class="text-gray-500">Aucune catégorie disponible pour le moment.</p>
    <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($categories as $cat): ?>
                <a href="/categorie/<?= e(categoryCanonicalPath($cat, $allCategories)) ?>"
                   class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition text-center">
                    <h3 class="font-semibold text-gray-800"><?= e($cat->getName()) ?></h3>
                    <?php if ($cat->getDescription()): ?>
                        <p class="text-sm text-gray-500 mt-1 line-clamp-2"><?= e($cat->getDescription()) ?></p>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- PRODUITS PHARES -->
<?php if (!empty($featured)): ?>
<section>
    <h2 class="text-2xl font-bold text-gray-800 mb-6"><?= se('home.featured_title') ?></h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($featured as $product): ?>
            <?php include BASE_PATH . '/templates/site/partials/product-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
