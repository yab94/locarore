<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Formulaire principal -->
    <div class="lg:col-span-2">
        <form id="product-form" method="post"
              action="<?= $product ? $url('Admin\Product.update', ['id' => $product->getId()]) : $url('Admin\Product.store') ?>"
              class="bg-white rounded-xl border border-gray-200 p-8 space-y-5">
            <?= require 'partials/csrf.php' ?>

            <!-- Catégorie principale -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie principale *</label>
                <select name="category_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                    <option value="">— Choisir —</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat->getId() ?>"
                            <?= $product && $product->getCategoryId() === $cat->getId() ? 'selected' : '' ?>>
                            <?= $html($cat->getName()) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Catégories supplémentaires -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Catégories supplémentaires
                    <span class="text-gray-400 font-normal text-xs ml-1">— Ctrl+clic pour sélection multiple</span>
                </label>
                <select name="extra_category_ids[]" multiple
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 h-28">
                    <?php
                    $currentCatIds = $product ? $product->getCategoryIds() : [];
                    foreach ($categories as $cat): ?>
                        <option value="<?= $cat->getId() ?>"
                            <?= in_array($cat->getId(), $currentCatIds) ? 'selected' : '' ?>>
                            <?= $html($cat->getName()) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Nom -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                <input type="text" name="name" id="name" required
                       value="<?= $html($product?->getName() ?? '') ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
            </div>

            <!-- Slug personnalisable -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Slug (URL)
                    <span class="text-gray-400 font-normal text-xs ml-1">— généré automatiquement si vide</span>
                </label>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-400"><?= $config->getStringParam('seo.products_base_url'); ?>/</span>
                    <input type="text" name="slug" id="slug"
                           value="<?= $html($product?->getSlug() ?? '') ?>"
                           placeholder="ex: vase-en-verre"
                           pattern="[a-z0-9\-]+"
                           title="Uniquement des lettres minuscules, chiffres et tirets"
                           class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                </div>
                <p class="text-xs text-gray-400 mt-1">Uniquement : lettres minuscules, chiffres, tirets</p>
            </div>

            <!-- Description WYSIWYG -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <input type="hidden" name="description" id="description-input"
                       value="<?= $html($product?->getDescription() ?? '') ?>">
                <div id="description-editor"
                     class="border border-gray-300 rounded-b-lg bg-white"
                     style="min-height:140px"><?= $product?->getDescription() ?? '' ?></div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock physique *</label>
                    <p class="text-xs text-gray-400 mb-1">Unités disponibles immédiatement</p>
                    <input type="number" name="stock" min="0" required
                           value="<?= $product?->getStock() ?? 0 ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock à la demande</label>
                    <p class="text-xs text-gray-400 mb-1">Unités fabricables si réservé ⚒</p>
                    <input type="number" name="stock_on_demand" min="0"
                           value="<?= $product?->getStockOnDemand() ?? 0 ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Temps de fabrication unitaire (jours)</label>
                    <p class="text-xs text-gray-400 mb-1">Utilisé si stock à la demande (&gt; 0). 0 = immédiat.</p>
                    <input type="number" name="fabrication_time_days" min="0" step="0.01"
                           value="<?= number_format($product?->getFabricationTimeDays() ?? 0, 2, '.', '') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Forfait de base (€) *</label>
                    <p class="text-xs text-gray-400 mb-1">Couvre 1 à 2 jours de location</p>
                    <input type="number" name="price_base" min="0" step="0.01" required
                           value="<?= number_format($product?->getPriceBase() ?? 80, 2, '.', '') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supp./jour WE (€)</label>
                    <p class="text-xs text-gray-400 mb-1">Sam+dim inclus, ≤ 4 jours</p>
                    <input type="number" name="price_extra_weekend" min="0" step="0.01"
                           value="<?= number_format($product?->getPriceExtraWeekend() ?? 0, 2, '.', '') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supp./jour semaine (€)</label>
                    <p class="text-xs text-gray-400 mb-1">Sinon (hors WE ou &gt; 4 jours)</p>
                    <input type="number" name="price_extra_weekday" min="0" step="0.01"
                           value="<?= number_format($product?->getPriceExtraWeekday() ?? 15, 2, '.', '') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>

            <!-- Tags -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tags
                    <span class="text-gray-400 font-normal text-xs ml-1">— séparés par virgules</span>
                </label>
                <input type="text" name="tags"
                       value="<?= $html(implode(', ', array_map(fn($t) => $t->getName(), $productTags ?? []))) ?>"
                       placeholder="ex: Mariage, Art de la table, Décoration"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                <p class="text-xs text-gray-400 mt-1">Les tags sont créés automatiquement s'ils n'existent pas encore.</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="bg-brand-600 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-brand-700 transition text-sm">
                    <?= $product ? 'Enregistrer' : 'Créer le produit' ?>
                </button>
                <a href="<?= $url('Admin\Product.index') ?>"
                   class="px-6 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50 transition">
                    Annuler
                </a>
            </div>
        </form>
    </div>

    <!-- Photos (uniquement en édition) -->
    <?php if ($product): ?>
    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Ajouter une photo</h3>
            <form method="post" action="<?= $url('Admin\Product.uploadPhoto', ['id' => $product->getId()]) ?>"
                  enctype="multipart/form-data" class="space-y-3">
                <?= require 'partials/csrf.php' ?>
                <input type="file" name="photo" accept="<?= $config->getStringParam('upload.allowed_types') ?>" required
                       class="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-brand-600 file:text-white file:text-sm hover:file:bg-brand-700">
                <input type="text" name="photo_description"
                       placeholder="Description de la photo (alt/title SEO)"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                <button type="submit"
                        class="w-full bg-gray-800 text-white text-sm font-medium py-2 rounded-lg hover:bg-gray-900 transition">
                    Uploader
                </button>
            </form>
        </div>

        <?php $photos = $product->getPhotos(); ?>
        <?php if (!empty($photos)): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-700 mb-4">Photos (<?= count($photos) ?>)</h3>
                <div class="space-y-4">
                    <?php foreach ($photos as $photo): ?>
                        <div class="border border-gray-100 rounded-xl overflow-hidden">
                            <div class="flex gap-3 p-3 items-start">
                                <img src="<?= $html($photo->getPublicPath()) ?>"
                                     alt="<?= $html($photo->getDescription() ?? '') ?>"
                                     class="w-20 h-20 object-cover rounded-lg flex-shrink-0">
                                <div class="flex-1 min-w-0 space-y-2">
                                    <!-- Formulaire description -->
                                    <form method="post"
                                          action="<?= $url('Admin\Product.updatePhotoDescription', ['photoId' => $photo->getId()]) ?>"
                                          class="flex gap-2">
                                        <?= require 'partials/csrf.php' ?>
                                        <input type="text" name="description"
                                               value="<?= $html($photo->getDescription() ?? '') ?>"
                                               placeholder="Description (alt/title SEO)..."
                                               class="flex-1 border border-gray-300 rounded-lg px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-brand-600">
                                        <button type="submit"
                                                class="bg-brand-600 text-white text-xs px-3 py-1 rounded-lg hover:bg-brand-700 transition flex-shrink-0">
                                            ✓
                                        </button>
                                    </form>
                                    <!-- Formulaire suppression -->
                                    <form method="post"
                                          action="<?= $url('Admin\Product.deletePhoto', ['photoId' => $photo->getId()]) ?>">
                                        <?= require 'partials/csrf.php' ?>
                                        <button type="submit"
                                                class="text-red-600 text-xs hover:underline"
                                                data-confirm="Supprimer cette photo ?">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
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
document.getElementById('product-form').addEventListener('submit', function () {
    document.getElementById('description-input').value =
        document.querySelector('#description-editor .ql-editor').innerHTML;
});
</script>

<?php if ($product && isset($calendarEvents)): ?>
<!-- Calendrier de disponibilité (fiche admin uniquement) -->
<section class="mt-10">
    <h2 class="text-lg font-semibold text-gray-700 mb-4">Calendrier de disponibilité</h2>
    <div id="product-calendar" class="bg-white border border-gray-200 rounded-xl p-6 overflow-x-auto"></div>
</section>

<script>
(function () {
    const reservedPeriods = <?= json_encode($calendarEvents) ?>;

    function getStatus(date) {
        const d = date.toISOString().slice(0, 10);
        const match = reservedPeriods.find(p => d >= p.start && d <= p.end);
        return match ? match.status : null;
    }

    const container = document.getElementById('product-calendar');
    const today = new Date();
    const monthsToShow = 6;

    for (let m = 0; m < monthsToShow; m++) {
        const year     = today.getFullYear() + Math.floor((today.getMonth() + m) / 12);
        const month    = (today.getMonth() + m) % 12;
        const firstDay = new Date(year, month, 1);
        const lastDay  = new Date(year, month + 1, 0);

        const monthEl = document.createElement('div');
        monthEl.className = 'inline-block mr-6 mb-6 align-top';

        const title = document.createElement('p');
        title.className = 'text-sm font-semibold text-gray-600 mb-2 text-center capitalize';
        title.textContent = firstDay.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' });
        monthEl.appendChild(title);

        const grid = document.createElement('div');
        grid.className = 'grid grid-cols-7 gap-0.5 text-xs text-center';

        ['Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa', 'Di'].forEach(d => {
            const h = document.createElement('div');
            h.className = 'text-gray-400 font-medium py-1';
            h.textContent = d;
            grid.appendChild(h);
        });

        const offset = (firstDay.getDay() + 6) % 7;
        for (let i = 0; i < offset; i++) grid.appendChild(document.createElement('div'));

        for (let day = 1; day <= lastDay.getDate(); day++) {
            const date   = new Date(year, month, day);
            const isPast = date < new Date(today.toDateString());
            const cell   = document.createElement('div');
            cell.textContent = day;
            cell.className = 'rounded py-1 ';
            const status = getStatus(date);
            if (isPast) {
                cell.className += 'text-gray-300';
            } else if (status === 'confirmed') {
                cell.className += 'bg-red-100 text-red-700 font-semibold';
                cell.title = 'Réservé (confirmé)';
            } else if (status === 'quoted') {
                cell.className += 'bg-orange-100 text-orange-700 font-semibold';
                cell.title = 'Devis envoyé';
            } else {
                cell.className += 'bg-green-50 text-green-700';
                cell.title = 'Disponible';
            }
            grid.appendChild(cell);
        }

        monthEl.appendChild(grid);
        container.appendChild(monthEl);
    }

    const legend = document.createElement('div');
    legend.className = 'mt-4 flex gap-6 text-xs text-gray-500 border-t border-gray-100 pt-4';
    legend.innerHTML = `
        <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded bg-green-100"></span> Disponible</span>
        <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded bg-orange-100"></span> Devis envoyé</span>
        <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded bg-red-100"></span> Réservé (confirmé)</span>
        <span class="flex items-center gap-1.5 text-gray-300"><span class="inline-block w-3 h-3 rounded bg-gray-100"></span> Passé</span>
    `;
    container.appendChild(legend);
})();
</script>
<?php endif; ?>
