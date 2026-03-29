<?php
$html     = RRB\View\HtmlEncoder::cast($tpl->get('html'));
$meta     = RRB\View\PageMeta::cast($tpl->get('meta'));
$config   = \RRB\Bootstrap\Config::cast($tpl->get('config'));
$siteName = $config->getString('app.name', '');
?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $html($meta->title) ?></title>
    <meta name="robots" content="<?= $html($meta->robots) ?>">
    <?php if ($meta->description !== ''): ?>
    <meta name="description" content="<?= $html($meta->description) ?>">
    <?php endif; ?>
    <?php if ($meta->keywords !== ''): ?>
    <meta name="keywords" content="<?= $html($meta->keywords) ?>">
    <?php endif; ?>
    <?php if ($meta->canonicalUrl !== ''): ?>
    <link rel="canonical" href="<?= $html($meta->canonicalUrl) ?>">
    <?php endif; ?>
    <!-- Open Graph / Twitter Card -->
    <meta property="og:type"        content="<?= $html($meta->ogType) ?>">
    <meta property="og:locale"      content="fr_FR">
    <?php if ($siteName !== ''): ?>
    <meta property="og:site_name"   content="<?= $html($siteName) ?>">
    <?php endif ?>
    <meta property="og:title"       content="<?= $html($meta->title) ?>">
    <?php if ($meta->description !== ''): ?>
    <meta property="og:description" content="<?= $html($meta->description) ?>">
    <?php endif; ?>
    <?php if ($meta->canonicalUrl !== ''): ?>
    <meta property="og:url"         content="<?= $html($meta->canonicalUrl) ?>">
    <?php endif; ?>
    <?php if ($meta->ogImage !== ''): ?>
    <meta property="og:image"       content="<?= $html($meta->ogImage) ?>">
    <?php if ($meta->ogImageWidth > 0): ?>
    <meta property="og:image:width"  content="<?= $meta->ogImageWidth ?>">
    <meta property="og:image:height" content="<?= $meta->ogImageHeight ?>">
    <?php endif; ?>
    <meta name="twitter:card"       content="summary_large_image">
    <meta name="twitter:image"      content="<?= $html($meta->ogImage) ?>">
    <?php else: ?>
    <meta name="twitter:card"       content="summary">
    <?php endif; ?>
    <meta name="twitter:title"      content="<?= $html($meta->title) ?>">
    <?php if ($meta->description !== ''): ?>
    <meta name="twitter:description" content="<?= $html($meta->description) ?>">
    <?php endif; ?>
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">
    <link rel="stylesheet" href="/assets/css/app.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'] . '/assets/css/app.css') ?>">
