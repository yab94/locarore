<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>

<div class="max-w-2xl">
    <form id="pack-form" method="post"
          action="<?= $pack ? $urlResolver->resolve(\Rore\Presentation\Controller\Admin\PackController::class . '.update', ['id' => $pack->getId()]) : $urlResolver->resolve(\Rore\Presentation\Controller\Admin\PackController::class . '.store') ?>"
          class="bg-white rounded-xl border border-gray-200 p-8 space-y-6">
        <?= require BASE_PATH . '/templates/partials/csrf.php' ?>

        <!-- Nom -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom du pack *</label>
            <input type="text" name="name" id="name" required
                   value="<?= \Rore\Presentation\Template\Html::e($pack?->getName() ?? '') ?>"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>

        <!-- Slug -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Slug (URL)
                <span class="text-gray-400 font-normal text-xs ml-1">— généré automatiquement si vide</span>
            </label>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-400">/pack/</span>
                <input type="text" name="slug" id="slug"
                       value="<?= \Rore\Presentation\Template\Html::e($pack?->getSlug() ?? '') ?>"
                       placeholder="ex: pack-mariage-boheme"
                       pattern="[a-z0-9\-]+"
                       class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
            </div>
        </div>

        <!-- Description WYSIWYG -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <input type="hidden" name="description" id="description-input"
                   value="<?= \Rore\Presentation\Template\Html::e($pack?->getDescription() ?? '') ?>">
            <div id="description-editor"
                 class="border border-gray-300 rounded-b-lg bg-white"
                 style="min-height:120px"><?= $pack?->getDescription() ?? '' ?></div>
        </div>

        <!-- Prix -->
        <div class="w-48">
            <label class="block text-sm font-medium text-gray-700 mb-1">Prix par jour (€) *</label>
            <input type="number" name="price_per_day" min="0" step="0.01" required
                   value="<?= number_format($pack?->getPricePerDay() ?? 0, 2, '.', '') ?>"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>

        <!-- Composition -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">
                Composition du pack
                <span class="text-gray-400 font-normal text-xs ml-1">— produits inclus</span>
            </label>

            <div id="pack-items" class="space-y-2">
                <?php
                $packItems = $pack ? $pack->getItems() : [];
                if (empty($packItems)):
                ?>
                    <!-- ligne vide par défaut -->
                    <div class="pack-item-row flex items-center gap-3">
                        <select name="item_product_id[]"
                                class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                            <option value="">— Choisir un produit —</option>
                            <?php foreach ($products as $prod): ?>
                                <option value="<?= $prod->getId() ?>"><?= \Rore\Presentation\Template\Html::e($prod->getName()) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="item_quantity[]" min="1" value="1"
                               class="w-20 border border-gray-300 rounded-lg px-3 py-2 text-sm text-center">
                        <button type="button" onclick="removePackItem(this)"
                                class="text-red-400 hover:text-red-600 text-lg font-bold leading-none">×</button>
                    </div>
                <?php else: ?>
                    <?php foreach ($packItems as $item): ?>
                    <div class="pack-item-row flex items-center gap-3">
                        <select name="item_product_id[]"
                                class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                            <option value="">— Choisir un produit —</option>
                            <?php foreach ($products as $prod): ?>
                                <option value="<?= $prod->getId() ?>"
                                    <?= $prod->getId() === $item->getProductId() ? 'selected' : '' ?>>
                                    <?= \Rore\Presentation\Template\Html::e($prod->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="item_quantity[]" min="1"
                               value="<?= $item->getQuantity() ?>"
                               class="w-20 border border-gray-300 rounded-lg px-3 py-2 text-sm text-center">
                        <button type="button" onclick="removePackItem(this)"
                                class="text-red-400 hover:text-red-600 text-lg font-bold leading-none">×</button>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <button type="button" onclick="addPackItem()"
                    class="mt-3 text-sm text-brand-600 hover:underline">
                + Ajouter un produit
            </button>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-brand-600 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-brand-700 transition text-sm">
                <?= $pack ? 'Enregistrer' : 'Créer le pack' ?>
            </button>
            <a href="<?= $urlResolver->resolve(\Rore\Presentation\Controller\Admin\PackController::class . '.index') ?>"
               class="px-6 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50 transition">
                Annuler
            </a>
        </div>
    </form>
</div>

<!-- Template HTML pour une nouvelle ligne de produit -->
<template id="pack-item-tpl">
    <div class="pack-item-row flex items-center gap-3">
        <select name="item_product_id[]"
                class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
            <option value="">— Choisir un produit —</option>
            <?php foreach ($products as $prod): ?>
                <option value="<?= $prod->getId() ?>"><?= \Rore\Presentation\Template\Html::e($prod->getName()) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="item_quantity[]" min="1" value="1"
               class="w-20 border border-gray-300 rounded-lg px-3 py-2 text-sm text-center">
        <button type="button" onclick="removePackItem(this)"
                class="text-red-400 hover:text-red-600 text-lg font-bold leading-none">×</button>
    </div>
</template>

<script>
function addPackItem() {
    const tpl = document.getElementById('pack-item-tpl');
    const row = tpl.content.cloneNode(true);
    document.getElementById('pack-items').appendChild(row);
}
function removePackItem(btn) {
    btn.closest('.pack-item-row').remove();
}

// Auto-slug
document.getElementById('name').addEventListener('input', function () {
    const slugField = document.getElementById('slug');
    if (slugField.dataset.manual) return;
    slugField.value = this.value
        .toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
});
document.getElementById('slug').addEventListener('input', function () {
    this.dataset.manual = '1';
});

new Quill('#description-editor', {
    theme: 'snow',
    modules: { toolbar: [['bold','italic','underline'],[{'header':[2,3,false]}],[{'list':'ordered'},{'list':'bullet'}],['link'],['clean']] }
});
document.getElementById('pack-form').addEventListener('submit', function () {
    document.getElementById('description-input').value =
        document.querySelector('#description-editor .ql-editor').innerHTML;
});
</script>
