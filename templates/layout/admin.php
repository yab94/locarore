<?php
$html    = Rore\Framework\View\HtmlHelper::cast($tpl->get('html'));
$url     = Rore\Framework\Http\UrlResolver::cast($tpl->get('url'));
$title   = \Rore\Framework\Support\Cast::string($tpl->tryGet('title', 'Admin — Locarore'));
$flash   = \Rore\Framework\Support\Cast::array($tpl->tryGet('flash', []));
$content = \Rore\Framework\Support\Cast::string($tpl->get('content'));
?>
<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $html($title) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="h-full bg-gray-100 flex">

    <?= $partial('layout/admin/sidebar') ?>

    <!-- Main content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm px-8 py-4">
            <h1 class="text-xl font-semibold text-gray-800"><?= $html($title) ?></h1>
        </header>

        <main class="flex-1 overflow-y-auto p-8">

            <?= $partial('partials/flash') ?>

            <?= $content ?>

        </main>
    </div>

    <script src="/assets/js/app.js"></script>
</body>
</html>
