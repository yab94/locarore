<div class="max-w-sm mx-auto mt-20">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6 text-center">Administration</h1>
        <form method="post" action="<?= $url('Admin\Auth.processLogin') ?>" class="space-y-4">
            <?= require 'partials/csrf.php' ?>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                <input type="password" name="password" required autofocus
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
            </div>
            <button type="submit"
                    class="w-full bg-brand-600 text-white font-semibold py-3 rounded-xl hover:bg-brand-700 transition">
                Se connecter
            </button>
        </form>
    </div>
</div>
