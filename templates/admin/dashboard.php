<?php
use Rore\Presentation\Template\HtmlHelper;
use Rore\Presentation\Seo\UrlResolver;

$html            = HtmlHelper::cast($tpl->get('html'));
$url             = UrlResolver::cast($tpl->get('url'));
$countCategories = (int) $tpl->tryGet('countCategories', 0);
$countProducts   = (int) $tpl->tryGet('countProducts', 0);
$pendingCount    = (int) $tpl->tryGet('pendingCount', 0);
$pendingList     = $tpl->tryGet('pendingList', []);
?>
<!-- Stats cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-sm text-gray-500">Catégories</p>
        <p class="text-3xl font-bold text-gray-900 mt-1"><?= $countCategories ?></p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-sm text-gray-500">Produits</p>
        <p class="text-3xl font-bold text-gray-900 mt-1"><?= $countProducts ?></p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-sm text-gray-500">Réservations en attente</p>
        <p class="text-3xl font-bold text-yellow-600 mt-1"><?= $pendingCount ?></p>
    </div>
</div>

<!-- Dernières demandes -->
<?php if (!empty($pendingList)): ?>
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h2 class="font-semibold text-gray-800">Demandes en attente</h2>
        <a href="<?= $url('Admin\Reservation.index') ?>?status=pending" class="text-sm text-brand-600 hover:underline">Voir tout</a>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
                <th class="px-6 py-3 text-left">Client</th>
                <th class="px-6 py-3 text-left">Dates</th>
                <th class="px-6 py-3 text-left">Reçu le</th>
                <th class="px-6 py-3 text-left">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($pendingList as $r): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3"><?= $html($r->getCustomerName()) ?></td>
                    <td class="px-6 py-3"><?= $html($r->getStartDate()->format('d/m/Y')) ?> → <?= $html($r->getEndDate()->format('d/m/Y')) ?></td>
                    <td class="px-6 py-3 text-gray-400"><?= $html($r->getCreatedAt()->format('d/m/Y')) ?></td>
                    <td class="px-6 py-3">
                        <a href="<?= $url('Admin\Reservation.show', ['id' => $r->getId()]) ?>" class="text-brand-600 hover:underline">Voir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
