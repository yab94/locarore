<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Détails -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="font-semibold text-gray-700 mb-4">Informations client</h2>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500">Nom</dt>
                    <dd class="font-medium text-gray-800"><?= e($reservation->getCustomerName()) ?></dd>
                </div>
                <div>
                    <dt class="text-gray-500">Email</dt>
                    <dd class="font-medium text-gray-800"><?= e($reservation->getCustomerEmail()) ?></dd>
                </div>
                <?php if ($reservation->getCustomerPhone()): ?>
                <div>
                    <dt class="text-gray-500">Téléphone</dt>
                    <dd class="font-medium text-gray-800"><?= e($reservation->getCustomerPhone()) ?></dd>
                </div>
                <?php endif; ?>
                <?php if ($reservation->getCustomerAddress()): ?>
                <div class="col-span-2">
                    <dt class="text-gray-500">Adresse client</dt>
                    <dd class="font-medium text-gray-800"><?= nl2br(e($reservation->getCustomerAddress())) ?></dd>
                </div>
                <?php endif; ?>
                <?php if ($reservation->getEventAddress()): ?>
                <div class="col-span-2">
                    <dt class="text-gray-500">Adresse de l'événement</dt>
                    <dd class="font-medium text-gray-800"><?= nl2br(e($reservation->getEventAddress())) ?></dd>
                </div>
                <?php endif; ?>
                <div>
                    <dt class="text-gray-500">Dates</dt>
                    <dd class="font-medium text-gray-800">
                        <?= dateRangeLabel($reservation->getStartDate(), $reservation->getEndDate()) ?>
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Statut</dt>
                    <dd>
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium <?= statusBadgeClass($reservation->getStatus()) ?>">
                            <?= statusLabel($reservation->getStatus()) ?>
                        </span>
                    </dd>
                </div>
            </dl>

            <?php if ($reservation->getNotes()): ?>
                <div class="mt-4 p-3 bg-gray-50 rounded-lg text-sm text-gray-600">
                    <?= nl2br(e($reservation->getNotes())) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Produits réservés -->
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="font-semibold text-gray-700 mb-4">Articles</h2>
            <table class="w-full text-sm">
                <thead class="text-gray-500 text-xs uppercase border-b">
                    <tr>
                        <th class="pb-2 text-left">Produit</th>
                        <th class="pb-2 text-right">Qté</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($reservation->getItems() as $item): ?>
                        <tr>
                            <td class="py-3">
                                <?php $p = $products[$item->getProductId()] ?? null; ?>
                                <?= $p ? e($p->getName()) : 'Produit #' . $item->getProductId() ?>
                            </td>
                            <td class="py-3 text-right"><?= $item->getQuantity() ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Actions -->
    <div class="space-y-4">
        <?php if ($reservation->isPending()): ?>
            <form method="post" action="/admin/reservations/<?= $reservation->getId() ?>/confirmer">
                <button type="submit"
                        class="w-full bg-green-600 text-white font-semibold py-3 rounded-xl hover:bg-green-700 transition">
                    ✓ Confirmer
                </button>
            </form>
        <?php endif; ?>

        <?php if (!$reservation->isCancelled()): ?>
            <form method="post" action="/admin/reservations/<?= $reservation->getId() ?>/annuler">
                <button type="submit"
                        class="w-full bg-red-50 text-red-700 border border-red-200 font-semibold py-3 rounded-xl hover:bg-red-100 transition"
                        data-confirm="Annuler cette réservation ?">
                    ✕ Annuler
                </button>
            </form>
        <?php endif; ?>

        <a href="/admin/reservations"
           class="block w-full text-center py-3 rounded-xl border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition">
            ← Retour
        </a>
    </div>
</div>
