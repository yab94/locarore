<div class="flex items-center justify-between mb-6">
    <div></div>
    <a href="/admin/categories/creer"
       class="bg-brand-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-brand-700 transition">
        + Nouvelle catégorie
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
                <th class="px-6 py-3 text-left">Nom</th>
                <th class="px-6 py-3 text-left">Slug</th>
                <th class="px-6 py-3 text-center">Statut</th>
                <th class="px-6 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($categories as $cat): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-800"><?= e($cat->getName()) ?></td>
                    <td class="px-6 py-4 text-gray-400 font-mono text-xs"><?= e($cat->getSlug()) ?></td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium
                            <?= $cat->isActive() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' ?>">
                            <?= $cat->isActive() ? 'Actif' : 'Inactif' ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-3">
                        <a href="/admin/categories/<?= $cat->getId() ?>/modifier"
                           class="text-brand-600 hover:underline text-sm">Modifier</a>
                        <form method="post" action="/admin/categories/<?= $cat->getId() ?>/toggle"
                              class="inline">
                            <button type="submit" class="text-gray-500 hover:text-gray-800 text-sm transition">
                                <?= $cat->isActive() ? 'Désactiver' : 'Activer' ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
