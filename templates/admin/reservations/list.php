<!-- Filtres statut -->
<div class="flex gap-2 mb-6 flex-wrap">
    <?php foreach (['all' => 'Toutes', 'pending' => 'En attente', 'quoted' => 'Devis envoyé', 'confirmed' => 'Confirmées', 'cancelled' => 'Annulées'] as $val => $label): ?>
        <a href="/admin/reservations?status=<?= $val ?>"
           class="px-3 py-1.5 rounded-lg text-sm font-medium border transition
               <?= ($currentStatus ?? 'all') === $val
                   ? 'bg-brand-600 text-white border-brand-600'
                   : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50' ?>">
            <?= $label ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
                <th class="px-6 py-3 text-left">#</th>
                <th class="px-6 py-3 text-left">Client</th>
                <th class="px-6 py-3 text-left">Dates</th>
                <th class="px-6 py-3 text-center">Statut</th>
                <th class="px-6 py-3 text-left">Reçu le</th>
                <th class="px-6 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php if (empty($reservations)): ?>
                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">Aucune réservation.</td></tr>
            <?php endif; ?>
            <?php foreach ($reservations as $r): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-gray-400"><?= $r->getId() ?></td>
                    <td class="px-6 py-4">
                        <p class="font-medium text-gray-800"><?= \Rore\Presentation\Template\Html::e($r->getCustomerName()) ?></p>
                        <p class="text-xs text-gray-400"><?= \Rore\Presentation\Template\Html::e($r->getCustomerEmail()) ?></p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?= \Rore\Presentation\Template\Html::e($r->getStartDate()->format('d/m/Y')) ?> → <?= e($r->getEndDate()->format('d/m/Y')) ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium <?= \Rore\Presentation\Reservation\ReservationStatusPresenter::badgeClass($r->getStatus()) ?>">
                            <?= \Rore\Presentation\Reservation\ReservationStatusPresenter::label($r->getStatus()) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-400"><?= \Rore\Presentation\Template\Html::e($r->getCreatedAt()->format('d/m/Y H:i')) ?></td>
                    <td class="px-6 py-4 text-right">
                        <a href="/admin/reservations/<?= $r->getId() ?>"
                           class="text-brand-600 hover:underline">Voir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
