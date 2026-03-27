<?php
use RRB\Http\UrlResolver;

$url = UrlResolver::cast($tpl->get('url'));
?>
<div class="max-w-xl mx-auto text-center py-16">
    <div class="text-5xl mb-6">✉️</div>
    <h1 class="text-3xl font-bold text-gray-900 mb-4">Message envoyé !</h1>
    <p class="text-gray-500 mb-8">
        Merci pour votre message. Nous vous répondrons dans les meilleurs délais.
    </p>
    <a href="<?= $url('Site\Home.index') ?>"
       class="inline-block bg-brand-600 text-white font-semibold px-6 py-3 rounded-xl hover:bg-brand-700 transition">
        Retour à l'accueil
    </a>
</div>
