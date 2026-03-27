<?php
use Rore\Framework\View\HtmlEncoder;
use Rore\Framework\Http\UrlResolver;
use Rore\Application\Settings\GetSettingUseCase;

$html     = HtmlEncoder::cast($tpl->get('html'));
$url      = UrlResolver::cast($tpl->get('url'));
$settings = GetSettingUseCase::cast($tpl->get('settings'));
// $partial is injected by the Template engine — not a param
?>
<div class="max-w-xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">
        <?= $html($settings->get('contact.page_title') ?: 'Contactez-nous') ?>
    </h1>
    <p class="text-gray-500 mb-8 text-sm">
        <?= $html($settings->get('contact.page_intro') ?: 'Une question ? Un projet ? Écrivez-nous.') ?>
    </p>

    <form method="post" action="<?= $url('Site\Contact.send') ?>"
          class="bg-white rounded-2xl border border-gray-200 p-8 space-y-5">
        <?= $partial('partials/csrf') ?>
        <input type="text" name="_trap" value="" style="display:none" tabindex="-1" autocomplete="off">

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Prénom *</label>
                <input type="text" name="first_name" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                <input type="text" name="last_name" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
            <input type="email" name="email" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
            <input type="tel" name="phone"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Objet *</label>
            <input type="text" name="subject" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Message *</label>
            <textarea name="content" rows="5" required
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600"></textarea>
        </div>

        <button type="submit"
                class="w-full bg-brand-600 text-white font-semibold py-3 rounded-xl hover:bg-brand-700 transition">
            Envoyer le message
        </button>
    </form>
</div>
