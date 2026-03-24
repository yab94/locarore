<footer class="bg-gray-900 mt-auto">
    <div class="container mx-auto px-4 max-w-6xl py-8 text-sm text-gray-400 text-center space-y-2">
        <div>&copy; <?= date('Y') ?> <?= $html($settings->get('site.name')) ?> — <?= $html($settings->get('site.tagline')) ?></div>
        <div>
            <a href="<?= $url('Site\Legal.mentions') ?>" class="hover:text-white hover:underline transition-colors">Mentions légales</a>
        </div>
    </div>
</footer>
