<?php
$html            = Rore\Presentation\Template\HtmlHelper::cast($tpl->get('html'));
$url             = Rore\Presentation\Seo\UrlResolver::cast($tpl->get('url'));
$urlResolver     = Rore\Presentation\Seo\UrlResolver::cast($tpl->get('urlResolver'));
$headerCategories = \Rore\Support\Cast::array($tpl->get('headerCategories'));
$settings        = Rore\Application\Settings\GetSettingUseCase::cast($tpl->get('settings'));
$cartItemCount   = (int) $tpl->tryGet('cartItemCount', 0);
?>
<header class="bg-white shadow-sm border-b-2 border-brand-600">
    <nav class="container mx-auto px-4 max-w-6xl h-16 flex items-center justify-between">
        <!-- Logo -->
        <img src="/assets/images/logo-latyana-evenements.png" alt="<?= $html($settings->get('site.name')) ?> logo" class="h-8 w-auto">
        <a href="/" class="text-xl font-bold text-brand-700 tracking-tight">
            <?= $html($settings->get('site.name')) ?>
        </a>

        <!-- Catégories principales -->
        <div class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-700 font-sans">
            <?php foreach ($headerCategories as $cat): ?>
                <a href="<?= $html($urlResolver->categoryUrl($cat, $headerCategories)) ?>"
                   class="hover:text-brand-700 transition-colors">
                    <?= $html($cat->getName()) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3">

        <!-- Contact -->
        <a href="<?= $url('Site\Contact.index') ?>" title="Nous contacter"
           class="text-gray-700 hover:text-brand-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </a>

        <!-- Panier -->
        <a href="<?= $url('Site\Cart.index') ?>" class="relative flex items-center gap-2 text-sm text-gray-700 hover:text-brand-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <?php $count = $cartItemCount; ?>
            <?php if ($count > 0): ?>
                <span class="absolute -top-2 -right-2 bg-brand-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                    <?= $count ?>
                </span>
            <?php endif; ?>
        </a>

        </div><!-- /actions -->
    </nav>
</header>
