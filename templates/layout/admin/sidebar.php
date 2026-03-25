<?php
$html = Rore\Framework\HtmlHelper::cast($tpl->get('html'));
$url  = Rore\Presentation\Seo\UrlResolver::cast($tpl->get('url'));
?>
    <!-- Sidebar -->
    <aside class="w-56 bg-gray-900 text-white flex flex-col min-h-screen">
        <div class="px-6 py-5 text-lg font-bold border-b border-gray-700">
            <a href="<?= $url('Admin\Dashboard.index') ?>" class="text-white hover:text-gray-300">Locarore Admin</a>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-1 text-sm">
            <a href="<?= $url('Admin\Dashboard.index') ?>"
               class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700 transition">
                Tableau de bord
            </a>
            <a href="<?= $url('Admin\Category.index') ?>"
               class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700 transition">
                Catégories
            </a>
            <a href="<?= $url('Admin\Product.index') ?>"
               class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700 transition">
                Produits
            </a>
            <a href="<?= $url('Admin\Pack.index') ?>"
               class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700 transition">
                🎁 Packs
            </a>
            <a href="<?= $url('Admin\Reservation.index') ?>"
               class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700 transition">
                Réservations
            </a>
            <a href="<?= $url('Admin\Reservation.calendar') ?>"
               class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700 transition">
                Calendrier
            </a>
            <a href="<?= $url('Admin\Message.index') ?>"
               class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700 transition">
                ✉️ Messages
            </a>
            <a href="<?= $url('Admin\Settings.index') ?>"
               class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700 transition">
                ✏️ Contenu
            </a>
        </nav>
        <div class="px-4 pb-6">
            <form method="post" action="<?= $url('Admin\Auth.logout') ?>">
                <?= $partial('partials/csrf') ?>
                <button type="submit"
                        class="w-full text-left px-3 py-2 rounded text-sm text-gray-400 hover:bg-gray-700 hover:text-white transition">
                    Déconnexion
                </button>
            </form>
        </div>
    </aside>
