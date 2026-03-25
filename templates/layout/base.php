<?php
$html    = Rore\Presentation\Template\HtmlHelper::cast($tpl->get('html'));
$meta    = $tpl->get('meta');
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
    <?php if ($meta->canonicalUrl !== null): ?>
    <link rel="canonical" href="<?= $html($meta->canonicalUrl) ?>">
    <?php endif; ?>
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">
    <!-- Polices Latyana Événements -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto+Slab:wght@400;600;700&display=swap" rel="stylesheet">
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
