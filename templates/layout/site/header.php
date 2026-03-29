<?php
$html            = RRB\View\HtmlEncoder::cast($tpl->get('html'));
$config         = RRB\Bootstrap\Config::cast($tpl->get('config'));
$url             = RRB\Http\UrlResolver::cast($tpl->get('url'));
$headerCategories = \RRB\Type\Cast::array($tpl->get('headerCategories'));
$cartItemCount   = (int) $tpl->tryGet('cartItemCount', 0);

$rootCategories = array_filter($headerCategories, fn($c) => $c->getParentId() === null);
?>
<header class="bg-white shadow-sm border-b-2 border-brand-600">
    <nav class="container mx-auto px-4 max-w-6xl h-16 flex items-center justify-between gap-4">

        <!-- Logo -->
        <a href="/" class="shrink-0">
            <img src="/assets/images/logo-latyana-evenements.png"
                 alt="Latyana-Événements"
                 title="Latyana-Événements - Location de décoration événementielle à <?=$html($config->getString('seo.location')) ?>"
                 class="h-14 w-auto" width="auto" height="56" fetchpriority="high">
        </a>

        <!-- Dropdown catégories + Recherche (desktop) -->
        <div class="hidden md:flex flex-1 items-center gap-3 max-w-lg">

            <!-- Dropdown catégories -->
            <div class="relative shrink-0" id="cat-menu">
                <button type="button"
                        id="cat-btn"
                        class="flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 text-gray-600 hover:text-brand-700 hover:border-brand-600 bg-white transition-colors"
                        title="Parcourir les catégories"
                        aria-label="Catégories"
                        aria-haspopup="true"
                        aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div id="cat-dropdown"
                     style="display:none"
                     class="absolute left-0 top-full mt-1.5 min-w-[280px] bg-white border border-gray-200 rounded-xl shadow-xl z-50 py-2 max-h-[70vh] overflow-y-auto">
                    <?php foreach ($rootCategories as $root): ?>

                        <!-- Racine -->
                        <a href="<?= $html($slug->categoryUrl($root, $headerCategories)) ?>"
                           class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold text-gray-800 hover:bg-brand-50 hover:text-brand-700 transition-colors group">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brand-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                            </svg>
                            <span class="flex-1 whitespace-nowrap"><?= $html($root->getName()) ?></span>
                            <?php if ($root->hasChildren()): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-300 group-hover:text-brand-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            <?php endif; ?>
                        </a>

                        <?php if ($root->hasChildren()): ?>
                            <!-- Enfants avec connecteur arbre -->
                            <div class="ml-5 mb-1 border-l-2 border-gray-100">
                                <?php foreach ($root->getChildren() as $child): ?>
                                    <a href="<?= $html($slug->categoryUrl($child, $headerCategories)) ?>"
                                       class="relative flex items-center gap-2 pl-5 pr-4 py-1.5 text-xs text-gray-500 hover:bg-brand-50 hover:text-brand-600 transition-colors whitespace-nowrap">
                                        <span class="absolute left-0 top-1/2 w-4 h-px bg-gray-200"></span>
                                        <?= $html($child->getName()) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Barre de recherche -->
            <form method="get" action="/recherche" class="flex flex-1 items-center">
                <input
                    type="search"
                    name="q"
                    value="<?= $html((string) ($_GET['q'] ?? '')) ?>"
                    placeholder="Rechercher…"
                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                <button type="submit" class="ml-2 text-gray-700 hover:text-brand-700 transition-colors" title="Rechercher">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                </button>
            </form>

        </div>

        <!-- Actions -->
        <div class="flex items-center gap-4 shrink-0">

            <!-- Recherche (mobile uniquement) -->
            <a href="/recherche" title="Rechercher"
               class="md:hidden text-gray-700 hover:text-brand-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
            </a>

            <!-- Contact -->
            <a href="<?= $url('Site\Contact.index') ?>" title="Nous contacter"
               class="text-gray-700 hover:text-brand-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </a>

            <!-- Panier -->
            <a href="<?= $url('Site\Cart.index') ?>" class="relative text-gray-700 hover:text-brand-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <?php if ($cartItemCount > 0): ?>
                    <span class="absolute -top-2 -right-2 bg-brand-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                        <?= $cartItemCount ?>
                    </span>
                <?php endif; ?>
            </a>

        </div>
    </nav>
</header>

<script>
(function() {
    var btn      = document.getElementById('cat-btn');
    var dropdown = document.getElementById('cat-dropdown');
    if (!btn || !dropdown) return;

    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var open = dropdown.style.display === 'block';
        dropdown.style.display = open ? 'none' : 'block';
        btn.setAttribute('aria-expanded', String(!open));
    });

    document.addEventListener('click', function() {
        dropdown.style.display = 'none';
        btn.setAttribute('aria-expanded', 'false');
    });
})();
</script>
