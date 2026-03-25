<?php
$html    = Rore\Framework\HtmlHelper::cast($tpl->get('html'));
$flash   = \Rore\Framework\Cast::array($tpl->tryGet('flash', []));
$content = \Rore\Framework\Cast::string($tpl->get('content'));
?>
<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <?= $partial('layout/site/meta') ?>
</head>
<body class="h-full bg-gray-50 flex flex-col font-sans antialiased">

    <?= $partial('layout/site/header') ?>

    <main class="flex-1 container mx-auto px-4 py-8 max-w-6xl">

        <?= $partial('partials/flash') ?>

        <?= $content ?>

    </main>

    <?= $partial('layout/site/footer') ?>

    <script src="/assets/js/app.js"></script>
</body>
</html>
