<?php
$html    = Rore\Framework\View\HtmlEncoder::cast($tpl->get('html'));
$title   = \Rore\Framework\Type\Cast::string($tpl->tryGet('title', 'Administration'));
$flash   = \Rore\Framework\Type\Cast::array($tpl->tryGet('flash', []));
$content = \Rore\Framework\Type\Cast::string($tpl->get('content'));
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
<body class="h-full bg-gray-100">

    <main class="min-h-full flex flex-col justify-center py-12 px-4">

        <?= $partial('partials/flash') ?>

        <?= $content ?>

    </main>

    <script src="/assets/js/app.js"></script>
</body>
</html>
