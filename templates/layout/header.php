<?php
$cart = \Rore\Application\Cart\CartSession::getInstance();
?>
<header class="bg-white shadow-sm">
    <nav class="container mx-auto px-4 max-w-6xl h-16 flex items-center justify-between">
        <!-- Logo -->
        <a href="/" class="text-xl font-bold text-brand-700 tracking-tight">
            <?= \Rore\Infrastructure\Cms\SettingsStore::html('site.name') ?>
        </a>

        <!-- Catégories principales -->
        <div class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-600">
            <?php
            static $headerCategories = null;
            if ($headerCategories === null) {
                $headerCategories = (new \Rore\Infrastructure\Persistence\MySqlCategoryRepository())->findAllActive();
            }
            foreach ($headerCategories as $cat): ?>
                <a href="/categorie/<?= \Rore\Presentation\Template\Html::e(\Rore\Presentation\Seo\CanonicalUrlResolver::categoryPath($cat, $headerCategories)) ?>"
                   class="hover:text-brand-700 transition-colors">
                    <?= \Rore\Presentation\Template\Html::e($cat->getName()) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Panier -->
        <a href="/panier" class="relative flex items-center gap-2 text-sm text-gray-700 hover:text-brand-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <?php $count = $cart->getItemCount(); ?>
            <?php if ($count > 0): ?>
                <span class="absolute -top-2 -right-2 bg-brand-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                    <?= $count ?>
                </span>
            <?php endif; ?>
        </a>
    </nav>
</header>
