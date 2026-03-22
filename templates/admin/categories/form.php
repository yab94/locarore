<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
<script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>

<div class="max-w-xl">
    <form method="post"
          action="<?= $category ? '/admin/categories/' . $category->getId() . '/modifier' : '/admin/categories/creer' ?>"
          class="bg-white rounded-xl border border-gray-200 p-8 space-y-5">

        <!-- Catégorie parente -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie parente</label>
            <select name="parent_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                <option value="">— Aucune (catégorie racine) —</option>
                <?php foreach ($categories as $cat):
                    if ($category && $cat->getId() === $category->getId()) continue;
                ?>
                    <option value="<?= $cat->getId() ?>"
                        <?= ($category && $category->getParentId() === $cat->getId()) ? 'selected' : '' ?>>
                        <?= e($cat->getName()) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Nom -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
            <input type="text" name="name" id="name" required
                   value="<?= e($category?->getName() ?? '') ?>"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>

        <!-- Slug personnalisable -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Slug (URL)
                <span class="text-gray-400 font-normal text-xs ml-1">— généré automatiquement si vide</span>
            </label>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-400">/categorie/</span>
                <input type="text" name="slug" id="slug"
                       value="<?= e($category?->getSlug() ?? '') ?>"
                       placeholder="ex: deco-florale"
                       pattern="[a-z0-9\-]+"
                       title="Uniquement des lettres minuscules, chiffres et tirets"
                       class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
            </div>
            <p class="text-xs text-gray-400 mt-1">Uniquement : lettres minuscules, chiffres, tirets</p>
        </div>

        <!-- Description WYSIWYG -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" id="description"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
            ><?= e($category?->getDescription() ?? '') ?></textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-brand-600 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-brand-700 transition text-sm">
                <?= $category ? 'Enregistrer' : 'Créer' ?>
            </button>
            <a href="/admin/categories"
               class="px-6 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50 transition">
                Annuler
            </a>
        </div>
    </form>
</div>

<script>
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

new EasyMDE({
    element: document.getElementById('description'),
    spellChecker: false,
    toolbar: ['bold', 'italic', 'heading', '|', 'unordered-list', 'ordered-list', '|', 'link', '|', 'preview'],
    minHeight: '120px',
    status: false,
});
</script>
