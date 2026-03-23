<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
<script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>

<form method="post" action="/admin/contenu" class="space-y-10">

    <!-- ── Textes courts ─────────────────────────────────────────────── -->
    <div class="bg-white rounded-xl border border-gray-200 p-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-1">Textes & libellés</h2>
        <p class="text-sm text-gray-400 mb-6">
            Clés courtes utilisées dans les templates.
            Vous pouvez utiliser <code class="bg-gray-100 px-1 rounded">{variable}</code> pour des valeurs dynamiques.
        </p>

        <?php
        $groups = [];
        foreach ($texts as $s) {
            $groups[$s->getGroup()][] = $s;
        }
        $groupLabels = [
            'general'     => '⚙ Général',
            'home'        => '🏠 Page d\'accueil',
            'reservation' => '📋 Réservation',
        ];
        foreach ($groups as $group => $items): ?>
            <div class="mb-8">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4 border-b pb-2">
                    <?= e($groupLabels[$group] ?? $group) ?>
                </h3>
                <div class="space-y-4">
                    <?php foreach ($items as $s): ?>
                        <div class="grid grid-cols-3 gap-4 items-start">
                            <label class="text-sm font-medium text-gray-700 pt-2">
                                <?= e($s->getLabel()) ?>
                                <span class="block text-xs text-gray-400 font-normal font-mono"><?= e($s->getKey()) ?></span>
                            </label>
                            <div class="col-span-2">
                                <input type="text"
                                       name="settings[<?= e($s->getKey()) ?>]"
                                       value="<?= e($s->getValue() ?? '') ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Blocs riches (Markdown / WYSIWYG) ─────────────────────────── -->
    <?php if (!empty($richtexts)): ?>
    <div class="bg-white rounded-xl border border-gray-200 p-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-1">Blocs de contenu</h2>
        <p class="text-sm text-gray-400 mb-6">Zones éditables affichées sur le site (Markdown).</p>

        <?php foreach ($richtexts as $s): ?>
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    <?= e($s->getLabel()) ?>
                    <span class="text-xs text-gray-400 font-normal font-mono ml-2"><?= e($s->getKey()) ?></span>
                </label>
                <textarea name="settings[<?= e($s->getKey()) ?>]"
                          id="richtext-<?= e(str_replace('.', '-', $s->getKey())) ?>"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                ><?= e($s->getValue() ?? '') ?></textarea>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ── Actions ───────────────────────────────────────────────────── -->
    <div class="flex gap-3">
        <button type="submit"
                class="bg-brand-600 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-brand-700 transition text-sm">
            Enregistrer
        </button>
    </div>

</form>

<script>
document.querySelectorAll('[id^="richtext-"]').forEach(function (el) {
    new EasyMDE({
        element: el,
        spellChecker: false,
        toolbar: ['bold', 'italic', 'heading', '|', 'unordered-list', 'ordered-list', '|', 'link', '|', 'preview'],
        minHeight: '150px',
    });
});
</script>
