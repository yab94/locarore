<?php /** @var string|null $errorMessage */ $errorMessage = $tpl->tryGet('errorMessage', null); ?>
<div class="flex items-center justify-center h-full min-h-[60vh] text-center">
    <div>
        <p class="text-8xl font-bold text-gray-200 mb-4">500</p>
        <p class="text-xl font-semibold text-gray-700 mb-2">Erreur serveur</p>
        <p class="text-gray-500 mb-6">Une erreur inattendue s'est produite. Veuillez réessayer.</p>
        <a href="/" class="bg-brand-600 text-white font-semibold px-6 py-3 rounded-xl hover:bg-brand-700 transition">
            Retour à l'accueil
        </a>
        <?php if (!empty($errorMessage)): ?>
        <pre class="mt-8 text-left text-xs bg-gray-100 rounded-xl p-4 overflow-auto max-w-2xl mx-auto text-red-700"><?= htmlspecialchars($errorMessage) ?></pre>
        <?php endif ?>
    </div>
</div>
