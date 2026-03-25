<?php
$html    = Rore\Presentation\Template\HtmlHelper::cast($tpl->get('html'));
$meta    = Rore\Presentation\Seo\PageMeta::cast($tpl->get('meta'));
$flash   = \Rore\Support\Cast::array($tpl->tryGet('flash', []));
$content = \Rore\Support\Cast::string($tpl->get('content'));
?>
<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
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
    <meta property="og:type"        content="website">
    <meta property="og:locale"      content="fr_FR">
    <meta property="og:title"       content="<?= $html($meta->title) ?>">
    <?php if ($meta->description !== ''): ?>
    <meta property="og:description" content="<?= $html($meta->description) ?>">
    <?php endif; ?>
    <?php if ($meta->canonicalUrl !== ''): ?>
    <meta property="og:url"         content="<?= $html($meta->canonicalUrl) ?>">
    <?php endif; ?>
    <?php if ($meta->ogImage !== ''): ?>
    <meta property="og:image"       content="<?= $html($meta->ogImage) ?>">
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
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="h-full bg-gray-50 flex flex-col font-sans antialiased">

    <?= $partial('layout/header') ?>

    <main class="flex-1 container mx-auto px-4 py-8 max-w-6xl">

        <?php if (!empty($flash)): ?>
            <?php foreach ($flash as $type => $msg): ?>
                <?php $cls = $type === 'error'
                    ? 'bg-red-100 border border-red-300 text-red-800'
                    : 'bg-green-100 border border-green-300 text-green-800'; ?>
                <div class="<?= $cls ?> rounded-lg px-4 py-3 mb-6 text-sm">
                    <?= $html($msg) ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?= $content ?>

    </main>

    <?= $partial('layout/footer') ?>

    <script src="/assets/js/app.js"></script>
</body>
</html>
