<?php
$tpl->assertArray('breadcrumb');
$tpl->assertArray('allCategories');
$tpl->assertInstanceOf('urlResolver', Rore\Presentation\Seo\UrlResolver::class);
$tpl->assertInstanceOf('html', Rore\Presentation\Template\HtmlHelper::class);
$finalCrumb = array_pop($breadcrumb);
?>
<nav class="text-sm text-gray-500 mb-6 flex flex-wrap items-center gap-1">
    <a href="/" class="hover:underline">Accueil</a>
    <?php foreach ($breadcrumb as $crumb): ?>
        <span>›</span>
        <a href="<?= $html($urlResolver->categoryUrl($crumb, $allCategories)) ?>" class="hover:underline">
            <?= $html($crumb->getName()) ?>
        </a>
    <?php endforeach; ?>
    <span>›</span>
    <span class="text-gray-800 font-medium"><?= $html($finalCrumb->getName()) ?></span>
</nav>
