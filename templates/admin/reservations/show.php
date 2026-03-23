<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Détails -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="font-semibold text-gray-700 mb-4">Informations client</h2>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500">Nom</dt>
                    <dd class="font-medium text-gray-800"><?= \Rore\Presentation\Template\Html::e($reservation->getCustomerName()) ?></dd>
                </div>
                <div>
                    <dt class="text-gray-500">Email</dt>
                    <dd class="font-medium text-gray-800"><?= \Rore\Presentation\Template\Html::e($reservation->getCustomerEmail()) ?></dd>
                </div>
                <?php if ($reservation->getCustomerPhone()): ?>
                <div>
                    <dt class="text-gray-500">Téléphone</dt>
                    <dd class="font-medium text-gray-800"><?= \Rore\Presentation\Template\Html::e($reservation->getCustomerPhone()) ?></dd>
                </div>
                <?php endif; ?>
                <?php if ($reservation->getCustomerAddress()): ?>
                <div class="col-span-2">
                    <dt class="text-gray-500">Adresse client</dt>
                    <dd class="font-medium text-gray-800"><?= nl2br(\Rore\Presentation\Template\Html::e($reservation->getCustomerAddress())) ?></dd>
                </div>
                <?php endif; ?>
                <?php if ($reservation->getEventAddress()): ?>
                <div class="col-span-2">
                    <dt class="text-gray-500">Adresse de l'événement</dt>
                    <dd class="font-medium text-gray-800"><?= nl2br(\Rore\Presentation\Template\Html::e($reservation->getEventAddress())) ?></dd>
                </div>
                <?php endif; ?>
                <div>
                    <dt class="text-gray-500">Dates</dt>
                    <dd class="font-medium text-gray-800">
                        <?= (new \Rore\Domain\Shared\ValueObject\DateRange($reservation->getStartDate(), $reservation->getEndDate()))->label() ?>
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Statut</dt>
                    <dd>
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium <?= \Rore\Presentation\Reservation\ReservationStatusPresenter::badgeClass($reservation->getStatus()) ?>">
                            <?= \Rore\Presentation\Reservation\ReservationStatusPresenter::label($reservation->getStatus()) ?>
                        </span>
                    </dd>
                </div>
            </dl>

            <?php if ($reservation->getNotes()): ?>
                <div class="mt-4 p-3 bg-gray-50 rounded-lg text-sm text-gray-600">
                    <?= nl2br(\Rore\Presentation\Template\Html::e($reservation->getNotes())) ?>
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
                        <th class="pb-2 text-right">Prix capturé</th>
                        <th class="pb-2 text-right">Prix actuel</th>
                        <th class="pb-2 text-right">Total capturé</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php
                    $grandTotalSnapshot = 0;
                    $grandTotalCurrent  = 0;
                    foreach ($reservation->getItems() as $item):
                        $p            = $products[$item->getProductId()] ?? null;
                        $snapshot     = $item->getUnitPriceSnapshot();
                        $currentPrice = $p ? $p->calculatePrice($reservation->getStartDate(), $reservation->getEndDate()) : null;
                        $totalSnap    = $snapshot !== null ? $snapshot * $item->getQuantity() : null;
                        $totalCurrent = $currentPrice !== null ? $currentPrice * $item->getQuantity() : null;
                        if ($totalSnap    !== null) $grandTotalSnapshot += $totalSnap;
                        if ($totalCurrent !== null) $grandTotalCurrent  += $totalCurrent;
                        $differs = $snapshot !== null && $currentPrice !== null && abs($snapshot - $currentPrice) > 0.001;
                    ?>
                        <tr>
                            <td class="py-3">
                                <?= $p ? \Rore\Presentation\Template\Html::e($p->getName()) : 'Produit #' . $item->getProductId() ?>
                            </td>
                            <td class="py-3 text-right"><?= $item->getQuantity() ?></td>
                            <td class="py-3 text-right font-medium">
                                <?= $snapshot !== null ? number_format($snapshot, 2, ',', ' ') . ' €' : '—' ?>
                            </td>
                            <td class="py-3 text-right <?= $differs ? 'text-orange-600 font-semibold' : 'text-gray-500' ?>">
                                <?= $currentPrice !== null ? number_format($currentPrice, 2, ',', ' ') . ' €' : '—' ?>
                                <?= $differs ? ' ⚠' : '' ?>
                            </td>
                            <td class="py-3 text-right font-semibold">
                                <?= $totalSnap !== null ? number_format($totalSnap, 2, ',', ' ') . ' €' : '—' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="border-t-2 border-gray-200 text-sm font-semibold">
                    <tr>
                        <td colspan="4" class="pt-3 text-right text-gray-600">Total capturé</td>
                        <td class="pt-3 text-right text-gray-900"><?= number_format($grandTotalSnapshot, 2, ',', ' ') ?> €</td>
                    </tr>
                    <?php if (abs($grandTotalSnapshot - $grandTotalCurrent) > 0.001): ?>
                    <tr>
                        <td colspan="4" class="pt-1 text-right text-orange-600 text-xs">Total au tarif actuel</td>
                        <td class="pt-1 text-right text-orange-600 text-xs"><?= number_format($grandTotalCurrent, 2, ',', ' ') ?> €</td>
                    </tr>
                    <?php endif; ?>
                </tfoot>
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
            . \Rore\Presentation\Security\CsrfField::render()
            . '<button type="submit" class="w-full font-semibold py-3 rounded-xl transition ' . $cls . '"'
            . ($confirm ? ' data-confirm="' . htmlspecialchars($confirm) . '"' : '') . '>'
            . $label . '</button></form>';
        // Bouton vers statut via /statut (champ caché)
        $btnStatus = fn(string $target, string $label, string $cls) =>
            '<form method="post" action="/admin/reservations/' . $id . '/statut">'
            . \Rore\Presentation\Security\CsrfField::render()
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
