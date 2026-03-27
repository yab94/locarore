<?php
use RRB\View\HtmlEncoder;
use RRB\Http\UrlResolver;
use Rore\Domain\Contact\Entity\ContactMessage;

$html    = HtmlEncoder::cast($tpl->get('html'));
$url     = UrlResolver::cast($tpl->get('url'));
$message = ContactMessage::cast($tpl->get('message'));
// $partial is injected by the Template engine — not a param
?>
<div class="max-w-2xl space-y-6">

    <!-- Infos expéditeur -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Expéditeur</h2>
        <dl class="grid grid-cols-2 gap-x-8 gap-y-3 text-sm">
            <div>
                <dt class="text-gray-400">Nom</dt>
                <dd class="font-medium text-gray-800"><?= $html($message->getFullName()) ?></dd>
            </div>
            <div>
                <dt class="text-gray-400">Email</dt>
                <dd>
                    <a href="mailto:<?= $html($message->getEmail()) ?>"
                       class="text-brand-600 hover:underline">
                        <?= $html($message->getEmail()) ?>
                    </a>
                </dd>
            </div>
            <?php if ($message->getPhone()): ?>
            <div>
                <dt class="text-gray-400">Téléphone</dt>
                <dd class="text-gray-800"><?= $html($message->getPhone()) ?></dd>
            </div>
            <?php endif; ?>
            <div>
                <dt class="text-gray-400">Reçu le</dt>
                <dd class="text-gray-800">
                    <?= $html($message->getCreatedAt()->format('d/m/Y à H:i')) ?>
                </dd>
            </div>
        </dl>
    </div>

    <!-- Contenu du message -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Objet</h2>
        <p class="font-medium text-gray-800 mb-6"><?= $html($message->getSubject()) ?></p>

        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Message</h2>
        <div class="text-gray-700 text-sm leading-relaxed whitespace-pre-wrap"><?= $html($message->getContent()) ?></div>
    </div>

    <!-- Actions -->
    <div class="flex items-center gap-4">
        <a href="<?= $url('Admin\Message.index') ?>"
           class="text-sm text-gray-500 hover:underline">← Retour à la liste</a>

        <?php if ($message->isRead()): ?>
            <form method="post"
                  action="<?= $url('Admin\Message.markUnread', ['id' => $message->getId()]) ?>">
                <?= $partial('partials/csrf') ?>
                <button type="submit"
                        class="px-4 py-2 text-sm rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                    Marquer non lu
                </button>
            </form>
        <?php else: ?>
            <form method="post"
                  action="<?= $url('Admin\Message.markRead', ['id' => $message->getId()]) ?>">
                <?= $partial('partials/csrf') ?>
                <button type="submit"
                        class="px-4 py-2 text-sm rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                    Marquer lu
                </button>
            </form>
        <?php endif; ?>

        <form method="post"
              action="<?= $url('Admin\Message.delete', ['id' => $message->getId()]) ?>"
              onsubmit="return confirm('Supprimer ce message définitivement ?')">
            <?= $partial('partials/csrf') ?>
            <button type="submit"
                    class="px-4 py-2 text-sm rounded-lg bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 transition">
                Supprimer
            </button>
        </form>
    </div>
</div>
