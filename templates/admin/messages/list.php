<?php
use Rore\Presentation\Template\HtmlHelper;
use Rore\Presentation\Seo\UrlResolver;
use Rore\Support\Cast;

$html     = HtmlHelper::cast($tpl->get('html'));
$url      = UrlResolver::cast($tpl->get('url'));
$messages = Cast::array($tpl->tryGet('messages', []));
// $partial is injected by the Template engine — not a param
?>
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
                <th class="px-6 py-3 text-left">Expéditeur</th>
                <th class="px-6 py-3 text-left">Objet</th>
                <th class="px-6 py-3 text-left">Reçu le</th>
                <th class="px-6 py-3 text-center">Statut</th>
                <th class="px-6 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php if (empty($messages)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-400">Aucun message.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($messages as $m): ?>
                <tr class="hover:bg-gray-50 <?= !$m->isRead() ? 'font-semibold' : '' ?>">
                    <td class="px-6 py-4">
                        <p class="text-gray-800"><?= $html($m->getFullName()) ?></p>
                        <p class="text-xs text-gray-400"><?= $html($m->getEmail()) ?></p>
                    </td>
                    <td class="px-6 py-4 text-gray-700"><?= $html($m->getSubject()) ?></td>
                    <td class="px-6 py-4 text-gray-400 whitespace-nowrap">
                        <?= $html($m->getCreatedAt()->format('d/m/Y H:i')) ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <?php if ($m->isRead()): ?>
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">Lu</span>
                        <?php else: ?>
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">Non lu</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-right space-x-3">
                        <a href="<?= $url('Admin\Message.show', ['id' => $m->getId()]) ?>"
                           class="text-brand-600 hover:underline">Voir</a>

                        <?php if ($m->isRead()): ?>
                            <form method="post"
                                  action="<?= $url('Admin\Message.markUnread', ['id' => $m->getId()]) ?>"
                                  class="inline">
                                <?= $partial('partials/csrf') ?>
                                <button type="submit" class="text-gray-500 hover:underline text-xs">
                                    Marquer non lu
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="post"
                                  action="<?= $url('Admin\Message.markRead', ['id' => $m->getId()]) ?>"
                                  class="inline">
                                <?= $partial('partials/csrf') ?>
                                <button type="submit" class="text-gray-500 hover:underline text-xs">
                                    Marquer lu
                                </button>
                            </form>
                        <?php endif; ?>

                        <form method="post"
                              action="<?= $url('Admin\Message.delete', ['id' => $m->getId()]) ?>"
                              class="inline"
                              onsubmit="return confirm('Supprimer ce message ?')">
                            <?= $partial('partials/csrf') ?>
                            <button type="submit" class="text-red-500 hover:underline text-xs">
                                Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
