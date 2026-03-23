<div class="max-w-lg mx-auto text-center py-16">
    <div class="text-5xl mb-6">🎉</div>
    <h1 class="text-3xl font-bold text-gray-900 mb-3"><?= se('confirmation.title') ?></h1>
    <p class="text-gray-600 mb-2">
        Votre demande de réservation <strong>#<?= e($reservationId) ?></strong> a bien été reçue.
    </p>
    <p class="text-gray-500 text-sm mb-8">
        <?= se('confirmation.message') ?>
    </p>
    <a href="/" class="bg-brand-600 text-white font-semibold px-6 py-3 rounded-xl hover:bg-brand-700 transition">
        Retour à l'accueil
    </a>
</div>
