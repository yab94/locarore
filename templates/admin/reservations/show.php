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
    <div class="space-y-3">

        <?php
        $s  = $reservation->getStatus();
        $id = $reservation->getId();
        // Helper local : bouton POST
        $btn = fn(string $action, string $label, string $cls, ?string $confirm = null) =>
            '<form method="post" action="/admin/reservations/' . $id . '/' . $action . '">'
            . '<button type="submit" class="w-full font-semibold py-3 rounded-xl transition ' . $cls . '"'
            . ($confirm ? ' data-confirm="' . htmlspecialchars($confirm) . '"' : '') . '>'
            . $label . '</button></form>';
        // Bouton vers statut via /statut (champ caché)
        $btnStatus = fn(string $target, string $label, string $cls) =>
            '<form method="post" action="/admin/reservations/' . $id . '/statut">'
            . '<input type="hidden" name="status" value="' . $target . '">'
            . '<button type="submit" class="w-full font-semibold py-3 rounded-xl transition ' . $cls . '">'
            . $label . '</button></form>';
        ?>

        <?php if ($s === 'pending'): ?>
            <?= $btn('devis',     '📄 Envoyer un devis', 'bg-orange-500 text-white hover:bg-orange-600') ?>
            <?= $btn('confirmer', '✓ Confirmer',          'bg-green-600 text-white hover:bg-green-700') ?>
        <?php endif; ?>

        <?php if ($s === 'quoted'): ?>
            <?= $btn('confirmer', '✓ Confirmer',                    'bg-green-600 text-white hover:bg-green-700') ?>
            <?= $btnStatus('pending', '↩ Remettre en attente',     'bg-gray-100 text-gray-700 hover:bg-gray-200') ?>
        <?php endif; ?>

        <?php if ($s === 'confirmed'): ?>
            <?= $btnStatus('quoted',   '↩ Repasser en devis',      'bg-orange-100 text-orange-700 hover:bg-orange-200') ?>
            <?= $btnStatus('pending',  '↩ Remettre en attente',    'bg-gray-100 text-gray-700 hover:bg-gray-200') ?>
        <?php endif; ?>

        <?php if ($s === 'cancelled'): ?>
            <?= $btnStatus('pending', '↩ Remettre en attente',     'bg-gray-100 text-gray-700 hover:bg-gray-200') ?>
        <?php endif; ?>

        <?php if ($s !== 'cancelled'): ?>
            <?= $btn('annuler', '✕ Annuler', 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-100', 'Annuler cette réservation ?') ?>
        <?php endif; ?>

        <a href="/admin/reservations"
           class="block w-full text-center py-3 rounded-xl border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition">
            ← Retour
        </a>
    </div>
</div>
