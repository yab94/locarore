<?php
use Rore\Framework\View\HtmlHelper;
use Rore\Framework\Http\UrlResolver;
use Rore\Framework\Type\Cast;
use Rore\Framework\Bootstrap\Config;
use Rore\Domain\Catalog\Entity\Category;

$html       = HtmlHelper::cast($tpl->get('html'));
$url        = UrlResolver::cast($tpl->get('url'));
$config     = Config::cast($tpl->get('config'));
$category   = Category::castOrNull($tpl->tryGet('category'));
$categories = Cast::array($tpl->tryGet('categories', []));
// $partial is injected by the Template engine — not a param
?>
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>

<div class="max-w-xl">
    <form id="category-form" method="post"
          action="<?= $category ? $url('Admin\Category.update', ['id' => $category->getId()]) : $url('Admin\Category.store') ?>"
          class="bg-white rounded-xl border border-gray-200 p-8 space-y-5">
        <?= $partial('partials/csrf') ?>

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
                        <?= $html($cat->getName()) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Nom -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
            <input type="text" name="name" id="name" required
                   value="<?= $html($category?->getName() ?? '') ?>"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
        </div>

        <!-- Slug personnalisable -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Slug (URL)
                <span class="text-gray-400 font-normal text-xs ml-1">— généré automatiquement si vide</span>
            </label>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-400"><?= $config->getString('seo.categories_base_url'); ?>/</span>
                <input type="text" name="slug" id="slug"
                       value="<?= $html($category?->getSlug() ?? '') ?>"
                       placeholder="ex: deco-florale"
                       pattern="[a-z0-9\-]+"
                       title="Uniquement des lettres minuscules, chiffres et tirets"
                       class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
            </div>
            <p class="text-xs text-gray-400 mt-1">Uniquement : lettres minuscules, chiffres, tirets</p>
        </div>

        <!-- Description courte -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Description courte
                <span class="text-gray-400 font-normal text-xs ml-1">— affichée dans les listes et les cartes</span>
            </label>
            <textarea name="description_short" rows="2"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600"
                      placeholder="Quelques mots pour décrire la catégorie..."
            ><?= $html($category?->getDescriptionShort() ?? '') ?></textarea>
        </div>

        <!-- Description longue WYSIWYG -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Description longue
                <span class="text-gray-400 font-normal text-xs ml-1">— affichée uniquement sur la page de la catégorie</span>
            </label>
            <input type="hidden" name="description" id="description-input"
                   value="<?= $html($category?->getDescription() ?? '') ?>">
            <div id="description-editor"
                 class="border border-gray-300 rounded-b-lg bg-white"
                 style="min-height:120px"><?= $category?->getDescription() ?? '' ?></div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-brand-600 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-brand-700 transition text-sm">
                <?= $category ? 'Enregistrer' : 'Créer' ?>
            </button>
            <a href="<?= $url('Admin\Category.index') ?>"
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

new Quill('#description-editor', {
    theme: 'snow',
    modules: { toolbar: [['bold','italic','underline'],[{'header':[2,3,false]}],[{'list':'ordered'},{'list':'bullet'}],['link'],['clean']] }
});
document.getElementById('category-form').addEventListener('submit', function () {
    document.getElementById('description-input').value =
        document.querySelector('#description-editor .ql-editor').innerHTML;
});
</script>
