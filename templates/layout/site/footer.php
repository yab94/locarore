<?php
$html     = RRB\View\HtmlEncoder::cast($tpl->get('html'));
$url      = RRB\Http\UrlResolver::cast($tpl->get('url'));
$config   = RRB\Bootstrap\Config::cast($tpl->get('config'));
?>
<footer class="bg-gray-900 mt-auto">
    <div class="container mx-auto px-4 max-w-6xl py-8 text-sm text-gray-400 text-center space-y-2">
        <div>&copy; <?= date('Y') ?> - Latyana-Événements - Location de décoration événementielle</div>
        <div class="text-gray-500">
            📍 <?= $html($config->getString('seo.city')) ?> – <?= $html($config->getString('seo.nearby_city')) ?> – <?= $html($config->getString('seo.region')) ?> (<?= substr($config->getString('seo.postal_code'), 0, 2) ?>) – <?= $html($config->getString('seo.region_full')) ?>
        </div>
        <div class="flex justify-center gap-6">
            <a href="<?= $url('Site\Legal.mentions') ?>" class="hover:text-white hover:underline transition-colors">Mentions légales</a>
            <a href="<?= $url('Site\Faq.index') ?>" class="hover:text-white hover:underline transition-colors">FAQ</a>
            <a href="<?= $url('Site\Contact.index') ?>" class="hover:text-white hover:underline transition-colors">Contact</a>
        </div>
    </div>
</footer>
