<?php
use Rore\Presentation\Template\HtmlHelper;
use Rore\Presentation\Seo\UrlResolver;
use Rore\Framework\Cast;
use Rore\Application\Settings\GetSettingUseCase;
use Rore\Domain\Reservation\Entity\Reservation;
use Rore\Domain\Shared\ValueObject\DateRange;

$html                 = HtmlHelper::cast($tpl->get('html'));
$url                  = UrlResolver::cast($tpl->get('url'));
$settings             = GetSettingUseCase::cast($tpl->get('settings'));
$reservation          = Reservation::cast($tpl->get('reservation'));
$dateRange            = DateRange::cast($tpl->get('dateRange'));
$products             = Cast::array($tpl->tryGet('products', []));
$packs                = Cast::array($tpl->tryGet('packs', []));
$productCurrentPrices = Cast::array($tpl->tryGet('productCurrentPrices', []));
// $partial is injected by the Template engine — not a param
?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Détails -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="font-semibold text-gray-700 mb-4">Informations client</h2>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500">Nom</dt>
                    <dd class="font-medium text-gray-800"><?= $html($reservation->getCustomerName()) ?></dd>
                </div>
                <div>
                    <dt class="text-gray-500">Email</dt>
                    <dd class="font-medium text-gray-800"><?= $html($reservation->getCustomerEmail()) ?></dd>
                </div>
                <?php if ($reservation->getCustomerPhone()): ?>
                <div>
                    <dt class="text-gray-500">Téléphone</dt>
                    <dd class="font-medium text-gray-800"><?= $html($reservation->getCustomerPhone()) ?></dd>
                </div>
                <?php endif; ?>
                <?php if ($reservation->getCustomerAddress()): ?>
                <div class="col-span-2">
                    <dt class="text-gray-500">Adresse client</dt>
                    <dd class="font-medium text-gray-800"><?= nl2br($html($reservation->getCustomerAddress())) ?></dd>
                </div>
                <?php endif; ?>
                <?php if ($reservation->getEventAddress()): ?>
                <div class="col-span-2">
                    <dt class="text-gray-500">Adresse de l'événement</dt>
                    <dd class="font-medium text-gray-800"><?= nl2br($html($reservation->getEventAddress())) ?></dd>
                </div>
                <?php endif; ?>
                <div>
                    <dt class="text-gray-500">Dates</dt>
                    <dd class="font-medium text-gray-800">
                        <?= $dateRange->label() ?>
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Statut</dt>
                    <dd>
                        <?php
                        $status = $reservation->getStatus();
                        $statusLabel = $settings->get('reservation.status.label.' . $status);
                        $statusLabel = $statusLabel !== '' ? $statusLabel : $status;
                        ?>
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium <?= $partial('partials/reservation-status-class', ['status' => $status]) ?>">
                            <?= $html($statusLabel) ?>
                        </span>
                    </dd>
                </div>
            </dl>

            <?php if ($reservation->getNotes()): ?>
                <div class="mt-4 p-3 bg-gray-50 rounded-lg text-sm text-gray-600">
                    <?= nl2br($html($reservation->getNotes())) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Articles réservés -->
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
                        if ($item->getPackId() !== null) continue; // packs traités en dessous
                        $p            = $products[$item->getProductId()] ?? null;
                        $snapshot     = $item->getUnitPriceSnapshot();
                        $currentPrice = $productCurrentPrices[$item->getProductId()] ?? null;
                        $totalSnap    = $snapshot !== null ? $snapshot * $item->getQuantity() : null;
                        $totalCurrent = $currentPrice !== null ? $currentPrice * $item->getQuantity() : null;
                        if ($totalSnap    !== null) $grandTotalSnapshot += $totalSnap;
                        if ($totalCurrent !== null) $grandTotalCurrent  += $totalCurrent;
                        $differs = $snapshot !== null && $currentPrice !== null && abs($snapshot - $currentPrice) > 0.001;
                    ?>
                        <tr>
                            <td class="py-3">
                                <?= $p ? $html($p->getName()) : 'Produit #' . $item->getProductId() ?>
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

        <!-- Packs réservés -->
        <?php
        $packItems = array_filter($reservation->getItems(), fn($item) => $item->getPackId() !== null);
        ?>
        <?php if (!empty($packItems)): ?>
        <div class="bg-white rounded-xl border border-brand-200 p-6">
            <h2 class="font-semibold text-gray-700 mb-4">Packs</h2>
            <table class="w-full text-sm">
                <thead class="text-gray-500 text-xs uppercase border-b">
                    <tr>
                        <th class="pb-2 text-left">Pack</th>
                        <th class="pb-2 text-right">Total capturé</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($packItems as $item):
                        $pack = $packs[$item->getPackId()] ?? null;
                    ?>
                        <tr>
                            <td class="py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs bg-brand-50 text-brand-700 border border-brand-200 rounded-full px-2 py-0.5 font-medium">Pack</span>
                                    <?= $pack ? $html($pack->getName()) : 'Pack #' . $item->getPackId() ?>
                                </div>
                            </td>
                            <td class="py-3 text-right font-semibold">
                                <?= $item->getUnitPriceSnapshot() !== null ? number_format($item->getUnitPriceSnapshot(), 2, ',', ' ') . ' €' : '—' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Actions -->
    <div class="space-y-3">

        <?php
        $s  = $reservation->getStatus();
        $id = $reservation->getId();
        // Pré-résolution des URLs d'action
        $_urlDevis     = $url('Admin\Reservation.quote',     ['id' => $id]);
        $_urlConfirmer = $url('Admin\Reservation.confirm',   ['id' => $id]);
        $_urlAnnuler   = $url('Admin\Reservation.cancel',    ['id' => $id]);
        $_urlStatut    = $url('Admin\Reservation.setStatus', ['id' => $id]);
        // Helper local : bouton POST
        $btn = fn(string $url, string $label, string $cls, ?string $confirm = null) =>
            '<form method="post" action="' . $url . '">'
            . $partial('partials/csrf')
            . '<button type="submit" class="w-full font-semibold py-3 rounded-xl transition ' . $cls . '"'
            . ($confirm ? ' data-confirm="' . htmlspecialchars($confirm) . '"' : '') . '>'
            . $label . '</button></form>';
        // Bouton vers statut via setStatus (champ caché)
        $btnStatus = fn(string $target, string $label, string $cls) =>
            '<form method="post" action="' . $_urlStatut . '">'
            . $partial('partials/csrf')
            . '<input type="hidden" name="status" value="' . $target . '">'
            . '<button type="submit" class="w-full font-semibold py-3 rounded-xl transition ' . $cls . '">'
            . $label . '</button></form>';
        ?>

        <?php if ($s === 'pending'): ?>
            <?= $btn($_urlDevis,     '📄 Envoyer un devis', 'bg-orange-500 text-white hover:bg-orange-600') ?>
            <?= $btn($_urlConfirmer, '✓ Confirmer',          'bg-green-600 text-white hover:bg-green-700') ?>
        <?php endif; ?>

        <?php if ($s === 'quoted'): ?>
            <?= $btn($_urlConfirmer, '✓ Confirmer',                    'bg-green-600 text-white hover:bg-green-700') ?>
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
            <?= $btn($_urlAnnuler, '✕ Annuler', 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-100', 'Annuler cette réservation ?') ?>
        <?php endif; ?>

        <a href="<?= $url('Admin\Reservation.index') ?>"
           class="block w-full text-center py-3 rounded-xl border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition">
            ← Retour
        </a>
    </div>
</div>
