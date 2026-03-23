<footer class="bg-white border-t mt-auto">
    <div class="container mx-auto px-4 max-w-6xl py-6 text-sm text-gray-500 text-center space-y-1">
        <div>&copy; <?= date('Y') ?> <?= \Rore\Presentation\Template\Html::e($settings->get('site.name')) ?> — <?= \Rore\Presentation\Template\Html::e($settings->get('site.tagline')) ?></div>
        <div>
            <a href="/mentions-legales" class="hover:text-gray-700 hover:underline">Mentions légales</a>
        </div>
    </div>
</footer>
