<?php
use RRB\View\HtmlEncoder;
use RRB\Type\Cast;

$html  = HtmlEncoder::cast($tpl->get('html'));
$items = Cast::array($tpl->tryGet('items', []));
?>
<div class="max-w-3xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Foire aux questions</h1>
    <p class="text-gray-500 mb-10 text-sm">Retrouvez les réponses aux questions les plus fréquentes.</p>

    <?php if (empty($items)): ?>
        <p class="text-gray-400 text-sm">Aucune question disponible pour le moment.</p>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($items as $item): ?>
                <details class="group bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <summary class="flex items-center justify-between px-6 py-4 cursor-pointer select-none
                                    list-none font-medium text-gray-800 hover:bg-gray-50 transition">
                        <span><?= $html($item->getQuestion()) ?></span>
                        <svg class="w-5 h-5 text-gray-400 shrink-0 transition-transform duration-200
                                    group-open:rotate-180"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>
                    <div class="px-6 pb-5 text-gray-600 text-sm leading-relaxed border-t border-gray-100 pt-4">
                        <?= $item->getAnswer() ?>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
