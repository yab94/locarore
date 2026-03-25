<?php
use Rore\Application\Settings\GetSettingUseCase;

$settings = GetSettingUseCase::cast($tpl->get('settings'));
?>
<div class="max-w-3xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Mentions légales</h1>
    <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">
        <?= $settings->get('mentions.content') ?>
    </div>
    <div class="mt-10">
        <a href="/" class="text-brand-600 hover:underline text-sm">← Retour à l'accueil</a>
    </div>
</div>
