<?php
use Rore\Application\Settings\GetSettingUseCase;

$settings = GetSettingUseCase::cast($tpl->get('settings'));
?>
<div class="max-w-3xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Mentions légales</h1>
    <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">
        <?= $settings->get('mentions.content') ?>
    </div>

    <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed mt-10">
        <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-3">Cookies</h2>
        <p>Le site utilise des cookies. Un cookie est un petit fichier texte déposé sur votre terminal lors de votre visite. Vous pouvez à tout moment modifier vos préférences via le bouton « Gérer mes préférences » présent dans le bandeau.</p>

        <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-2">Cookies strictement nécessaires</h3>
        <p>Ces cookies sont indispensables au fonctionnement du site. Ils ne peuvent pas être désactivés.</p>
        <table class="w-full text-sm border-collapse mt-3 mb-5">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border border-gray-300 px-3 py-2 text-left">Cookie</th>
                    <th class="border border-gray-300 px-3 py-2 text-left">Durée</th>
                    <th class="border border-gray-300 px-3 py-2 text-left">Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border border-gray-300 px-3 py-2 font-mono">PHPSESSID</td>
                    <td class="border border-gray-300 px-3 py-2">Session</td>
                    <td class="border border-gray-300 px-3 py-2">Maintient votre session (panier). Supprimé à la fermeture du navigateur.</td>
                </tr>
                <tr>
                    <td class="border border-gray-300 px-3 py-2 font-mono">cc_cookie</td>
                    <td class="border border-gray-300 px-3 py-2">6 mois</td>
                    <td class="border border-gray-300 px-3 py-2">Mémorise votre choix de consentement aux cookies.</td>
                </tr>
            </tbody>
        </table>

        <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-2">Cookies analytiques</h3>
        <p>Ces cookies nous permettent de mesurer l'audience du site et d'améliorer nos contenus. Ils ne sont déposés qu'avec votre consentement.</p>
        <table class="w-full text-sm border-collapse mt-3 mb-5">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border border-gray-300 px-3 py-2 text-left">Cookie</th>
                    <th class="border border-gray-300 px-3 py-2 text-left">Émetteur</th>
                    <th class="border border-gray-300 px-3 py-2 text-left">Durée</th>
                    <th class="border border-gray-300 px-3 py-2 text-left">Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border border-gray-300 px-3 py-2 font-mono">_ga</td>
                    <td class="border border-gray-300 px-3 py-2">Google Analytics</td>
                    <td class="border border-gray-300 px-3 py-2">2 ans</td>
                    <td class="border border-gray-300 px-3 py-2">Identifie de façon anonyme les visiteurs.</td>
                </tr>
                <tr>
                    <td class="border border-gray-300 px-3 py-2 font-mono">_ga_*</td>
                    <td class="border border-gray-300 px-3 py-2">Google Analytics</td>
                    <td class="border border-gray-300 px-3 py-2">2 ans</td>
                    <td class="border border-gray-300 px-3 py-2">Maintient l'état de session Google Analytics 4.</td>
                </tr>
                <tr>
                    <td class="border border-gray-300 px-3 py-2 font-mono">_gid</td>
                    <td class="border border-gray-300 px-3 py-2">Google Analytics</td>
                    <td class="border border-gray-300 px-3 py-2">24 heures</td>
                    <td class="border border-gray-300 px-3 py-2">Distingue les utilisateurs pour la session en cours.</td>
                </tr>
            </tbody>
        </table>
        <p>Pour en savoir plus : <a href="https://policies.google.com/privacy" target="_blank" rel="noopener" class="text-brand-600 hover:underline">Politique de confidentialité Google</a>.</p>
    </div>
    <div class="mt-10">
        <a href="/" class="text-brand-600 hover:underline text-sm">← Retour à l'accueil</a>
    </div>
</div>
