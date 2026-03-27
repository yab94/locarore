<?php
use Rore\Framework\View\HtmlHelper;
use Rore\Framework\Type\Cast;
use Rore\Application\Settings\GetSettingUseCase;

$html           = HtmlHelper::cast($tpl->get('html'));
$settings       = GetSettingUseCase::cast($tpl->get('settings'));
$reservationId  = Cast::int($tpl->get('reservationId'));
?>
<div class="max-w-lg mx-auto text-center py-16">
    <div class="text-5xl mb-6">🎉</div>
    <h1 class="text-3xl font-bold text-gray-900 mb-3"><?= $html($settings->get('confirmation.title')) ?></h1>
    <p class="text-gray-600 mb-2">
        Votre demande de réservation <strong>#<?= $html($reservationId) ?></strong> a bien été reçue.
    </p>
    <p class="text-gray-500 text-sm mb-8">
        <?= $html($settings->get('confirmation.message')) ?>
    </p>
    <a href="/" class="bg-brand-600 text-white font-semibold px-6 py-3 rounded-xl hover:bg-brand-700 transition">
        Retour à l'accueil
    </a>
</div>
