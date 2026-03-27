<?php
use Rore\Framework\View\HtmlHelper;
use Rore\Framework\Http\UrlResolver;
use Rore\Framework\Type\Cast;
use Rore\Application\Settings\GetSettingUseCase;
use Rore\Framework\Bootstrap\Config;

$html          = HtmlHelper::cast($tpl->get('html'));
$settings      = GetSettingUseCase::cast($tpl->get('settings'));
$url           = UrlResolver::cast($tpl->get('url'));
$config        = Config::cast($tpl->get('config'));
$allCategories = Cast::array($tpl->tryGet('allCategories', []));
$categories    = Cast::array($tpl->tryGet('categories', []));
$tags          = Cast::array($tpl->tryGet('tags', []));
$featured      = Cast::array($tpl->tryGet('featured', []));
// $partial is injected by the Template engine — not a param
?>

<!-- HERO -->
<section class="bg-brand-600 text-white py-16 rounded-2xl mb-10 text-center">
    <h1 class="text-4xl font-bold mb-3"><?= $html($settings->get('hero.title')) ?></h1>
    <p class="text-lg opacity-90 mb-6"><?= $html($settings->get('hero.subtitle')) ?></p>
    <a href="#categories" class="bg-white text-brand-700 font-semibold px-6 py-3 rounded-lg hover:bg-gray-100 transition">
        <?= $html($settings->get('hero.cta')) ?>
    </a>
</section>

<?php $homeIntro = $settings->get('home.intro'); ?>
<?php if ($homeIntro): ?>
<section class="mb-10 prose prose-sm max-w-none text-gray-700">
    <?= $homeIntro ?>
</section>
<?php endif; ?>

<!-- CATEGORIES -->
<section id="categories" class="mb-12">
    <h2 class="text-2xl font-bold text-gray-800 mb-6"><?= $html($settings->get('home.categories_title')) ?></h2>
    <?php if (empty($categories)): ?>
        <p class="text-gray-500">Aucune catégorie disponible pour le moment.</p>
    <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($categories as $cat): ?>
                <a href="<?= $html($slug->categoryUrl($cat, $allCategories)) ?>"
                   class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition text-center">
                    <h3 class="font-semibold text-gray-800"><?= $html($cat->getName()) ?></h3>
                    <?php if ($cat->getDescriptionShort()): ?>
                        <p class="text-sm text-gray-500 mt-1 line-clamp-2"><?= $html($cat->getDescriptionShort()) ?></p>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- TAGS -->
<?php if (!empty($tags)): ?>
<section class="mb-12">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Parcourir par thème</h2>
    <div class="flex flex-wrap gap-2">
        <?php foreach ($tags as $tag): ?>
            <a href="<?= $config->getString('seo.tags_base_url') ?>/<?= $html($tag->getSlug()) ?>"
               class="inline-flex items-center gap-1.5 bg-brand-50 text-brand-700 border border-brand-200 rounded-full px-4 py-1.5 text-sm font-medium hover:bg-brand-100 transition">
                🏷️ <?= $html($tag->getName()) ?>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- PRODUITS PHARES -->
<?php if (!empty($featured)): ?>
<section class="mb-12">
    <h2 class="text-2xl font-bold text-gray-800 mb-6"><?= $html($settings->get('home.featured_title')) ?></h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($featured as $product): ?>
            <?= $partial('partials/product-card', ['product' => $product]) ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- CTA CONTACT -->

<section class="mt-10 bg-brand-50 border border-brand-200 rounded-2xl py-8 px-6 text-center" style="padding-bottom:3rem;">
    <h2 class="text-2xl font-bold text-gray-800 mb-2">Vous avez un projet ou une question ?</h2>
    <p class="text-gray-600 mb-6">Notre équipe est disponible pour vous accompagner et vous établir un devis sur mesure.</p>
    <a href="<?= $url('Site\\Contact.index') ?>"
       class="inline-block bg-brand-600 text-white font-semibold px-8 py-3 rounded-xl hover:bg-brand-700 transition">
        Contactez-nous
    </a>
</section>
