<?php
$breadcrumb = RRB\Type\Cast::array($tpl->get('breadcrumb'));
$allCategories = RRB\Type\Cast::array($tpl->get('allCategories'));
$html = RRB\View\HtmlEncoder::cast($tpl->get('html'));
$finalCrumb = array_pop($breadcrumb);
?>
<nav class="text-sm text-gray-500 mb-6 flex flex-wrap items-center gap-1">
    <a href="/" class="hover:underline">Accueil</a>
    <?php foreach ($breadcrumb as $crumb): ?>
        <span>›</span>
        <a href="<?= $html($slug->categoryUrl($crumb, $allCategories)) ?>" class="hover:underline">
            <?= $html($crumb->getName()) ?>
        </a>
    <?php endforeach; ?>
    <span>›</span>
    <span class="text-gray-800 font-medium"><?= $html($finalCrumb->getName()) ?></span>
</nav>
