<?php
use Rore\Presentation\Seo\UrlResolver;

$url           = UrlResolver::cast($tpl->get('url'));
$cart          = $tpl->get('cart');
$cartDateRange = $tpl->get('cartDateRange');
// $partial is injected by the Template engine — not a param
?>
<div class="max-w-lg mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Finaliser ma réservation</h1>
    <p class="text-gray-500 mb-8 text-sm">
        📅 <?= $cartDateRange->label() ?>
        — <?= $cart->getItemCount() ?> article(s)
    </p>

    <form method="post" action="<?= $url('Site\Cart.processCheckout') ?>" class="bg-white rounded-2xl border border-gray-200 p-8 space-y-5">
        <?= $partial('partials/csrf') ?>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet *</label>
            <input type="text" name="customer_name" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
            <input type="email" name="customer_email" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
            <input type="tel" name="customer_phone"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>

        <hr class="border-gray-100">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Votre adresse
                <span class="text-gray-400 font-normal text-xs ml-1">— pour la livraison</span>
            </label>
            <textarea name="customer_address" rows="2"
                      placeholder="Rue, code postal, ville"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Adresse de l'événement
                <span class="text-gray-400 font-normal text-xs ml-1">— si différente de votre adresse</span>
            </label>
            <textarea name="event_address" rows="2"
                      placeholder="Rue, code postal, ville"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600"></textarea>
        </div>

        <hr class="border-gray-100">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Message / notes</label>
            <textarea name="notes" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600"></textarea>
        </div>

        <button type="submit"
                class="w-full bg-brand-600 text-white font-semibold py-3 rounded-xl hover:bg-brand-700 transition">
            Envoyer ma demande de réservation
        </button>
    </form>
</div>
