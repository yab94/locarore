<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Admin — Locarore') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#eef2ff',
                            100: '#e0e7ff',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full bg-gray-100 flex">

    <!-- Sidebar -->
    <aside class="w-56 bg-gray-900 text-white flex flex-col min-h-screen">
        <div class="px-6 py-5 text-lg font-bold border-b border-gray-700">
            <a href="/admin/dashboard" class="text-white hover:text-gray-300">Locarore Admin</a>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-1 text-sm">
            <a href="/admin/dashboard"
               class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700 transition">
                Tableau de bord
            </a>
            <a href="/admin/categories"
               class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700 transition">
                Catégories
            </a>
            <a href="/admin/produits"
               class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700 transition">
                Produits
            </a>
            <a href="/admin/reservations"
               class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700 transition">
                Réservations
            </a>
            <a href="/admin/reservations/calendrier"
               class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700 transition">
                Calendrier
            </a>
            <a href="/admin/packs"
               class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700 transition">
                🎁 Packs
            </a>
        </nav>
        <div class="px-4 pb-6">
            <form method="post" action="/admin/deconnexion">
                <button type="submit"
                        class="w-full text-left px-3 py-2 rounded text-sm text-gray-400 hover:bg-gray-700 hover:text-white transition">
                    Déconnexion
                </button>
            </form>
        </div>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm px-8 py-4">
            <h1 class="text-xl font-semibold text-gray-800"><?= e($title ?? '') ?></h1>
        </header>

        <main class="flex-1 overflow-y-auto p-8">

            <?php if (!empty($flash)): ?>
                <?php foreach ($flash as $type => $msg): ?>
                    <?php $cls = $type === 'error'
                        ? 'bg-red-100 border border-red-300 text-red-800'
                        : 'bg-green-100 border border-green-300 text-green-800'; ?>
                    <div class="<?= $cls ?> rounded-lg px-4 py-3 mb-6 text-sm">
                        <?= e($msg) ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?= $content ?>

        </main>
    </div>

    <script src="/assets/js/app.js"></script>
</body>
</html>
