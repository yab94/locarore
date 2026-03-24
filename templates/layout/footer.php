<footer class="bg-gray-900 mt-auto">
    <div class="container mx-auto px-4 max-w-6xl py-8 text-sm text-gray-400 text-center space-y-2">
        <div>&copy; <?= date('Y') ?> <?= \Rore\Presentation\Template\Html::e($settings->get('site.name')) ?> — <?= \Rore\Presentation\Template\Html::e($settings->get('site.tagline')) ?></div>
        <div>
            <a href="<?= $urlResolver->resolve(\Rore\Presentation\Controller\Site\LegalController::class . '.mentions') ?>" class="hover:text-white hover:underline transition-colors">Mentions légales</a>
        </div>
    </div>
</footer>
