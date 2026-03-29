<?php
$html    = RRB\View\HtmlEncoder::cast($tpl->get('html'));
$flash   = \RRB\Type\Cast::array($tpl->tryGet('flash', []));
$content = \RRB\Type\Cast::string($tpl->get('content'));
?>
<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <?= $partial('layout/site/meta') ?>
    <?= $partial('partials/analytics') ?>
</head>
<body class="h-full bg-gray-50 flex flex-col font-sans antialiased">

    <?= $partial('layout/site/header') ?>

    <main class="flex-1 container mx-auto px-4 py-8 max-w-6xl">

        <?= $partial('partials/flash') ?>

        <?= $content ?>

    </main>

    <?= $partial('layout/site/footer') ?>

    <script src="/assets/js/app.js"></script>
    <script>
    (function () {
        var _cs = {};
        window.carouselMove = function (id, dir) {
            var el = document.getElementById(id);
            if (!el) return;
            var track = el.querySelector('.carousel-track');
            var total = track.children.length;
            _cs[id] = (((_cs[id] ?? 0) + dir) % total + total) % total;
            _carouselApply(id, track, total);
        };
        window.carouselGo = function (id, index) {
            var el = document.getElementById(id);
            if (!el) return;
            var track = el.querySelector('.carousel-track');
            _cs[id] = index;
            _carouselApply(id, track, track.children.length);
        };
        window._carouselApply = function (id, track, total) {
            var idx = _cs[id] ?? 0;
            track.style.transform = 'translateX(-' + (idx * (100 / total)) + '%)';
            document.getElementById(id).querySelectorAll('.carousel-dot').forEach(function (dot, i) {
                dot.classList.toggle('bg-white', i === idx);
                dot.classList.toggle('bg-white/50', i !== idx);
            });
        };
    })();
    </script>
</body>
</html>
