<?php
$html     = RRB\View\HtmlEncoder::cast($tpl->get('html'));
$url      = RRB\Http\UrlResolver::cast($tpl->get('url'));
?>
<footer class="bg-gray-900 mt-auto">
    <div class="container mx-auto px-4 max-w-6xl py-8 text-sm text-gray-400 text-center space-y-2">
        <div>&copy; <?= date('Y') ?> Locarore — Location de décoration événementielle</div>
        <div class="flex justify-center gap-6">
            <a href="<?= $url('Site\Legal.mentions') ?>" class="hover:text-white hover:underline transition-colors">Mentions légales</a>
            <a href="<?= $url('Site\Faq.index') ?>" class="hover:text-white hover:underline transition-colors">FAQ</a>
            <a href="<?= $url('Site\Contact.index') ?>" class="hover:text-white hover:underline transition-colors">Contact</a>
        </div>
    </div>
</footer>
