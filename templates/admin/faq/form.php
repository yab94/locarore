<?php
use RRB\View\HtmlEncoder;
use RRB\Http\UrlResolver;
use Rore\Domain\Faq\Entity\FaqItem;

$html = HtmlEncoder::cast($tpl->get('html'));
$url  = UrlResolver::cast($tpl->get('url'));
$item = FaqItem::castOrNull($tpl->tryGet('item'));
// $partial is injected by the Template engine — not a param
?>
<div class="max-w-2xl">
    <form method="post"
          action="<?= $item ? $url('Admin\\Faq.update', ['id' => $item->getId()]) : $url('Admin\\Faq.store') ?>"
          class="bg-white rounded-xl border border-gray-200 p-8 space-y-5">
        <?= $partial('partials/csrf') ?>

        <!-- Question -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Question *</label>
            <input type="text" name="question" required
                   value="<?= $html($item?->getQuestion() ?? '') ?>"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>

        <!-- Réponse -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Réponse *</label>
            <textarea name="answer" rows="6" required
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600"
            ><?= $html($item?->getAnswer() ?? '') ?></textarea>
        </div>

        <!-- Ordre -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Ordre
                <span class="text-gray-400 font-normal text-xs ml-1">— les plus petits apparaissent en premier</span>
            </label>
            <input type="number" name="position" min="0"
                   value="<?= $item?->getPosition() ?? 0 ?>"
                   class="w-32 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>

        <!-- Visibilité -->
        <div class="flex items-center gap-3">
            <input type="hidden" name="is_visible" value="0">
            <input type="checkbox" id="is_visible" name="is_visible" value="1"
                   <?= ($item === null || $item->isVisible()) ? 'checked' : '' ?>
                   class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-600">
            <label for="is_visible" class="text-sm font-medium text-gray-700">
                Visible sur le site
            </label>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-brand-600 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-brand-700 transition text-sm">
                <?= $item ? 'Enregistrer' : 'Créer' ?>
            </button>
            <a href="<?= $url('Admin\\Faq.index') ?>"
               class="px-6 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50 transition">
                Annuler
            </a>
        </div>
    </form>
</div>
