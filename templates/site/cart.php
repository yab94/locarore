<h1 class="text-3xl font-bold text-gray-900 mb-8">Mon panier</h1>

<?php if (!$cart->hasDates()): ?>
    <!-- Étape 1 : Choisir les dates -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 max-w-lg mx-auto text-center">
        <p class="text-lg text-gray-700 mb-6">Commencez par choisir vos dates de location</p>
        <form method="post" action="<?= $urlResolver->resolve(\Rore\Presentation\Controller\Site\CartController::class . '.setDates') ?>" class="space-y-4">
            <?= require BASE_PATH . '/templates/partials/csrf.php' ?>
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de début</label>
                    <input type="date" name="start_date" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                           min="<?= date('Y-m-d') ?>">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin</label>
                    <input type="date" name="end_date" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                           min="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <button type="submit"
                    class="w-full bg-brand-600 text-white font-semibold py-3 rounded-xl hover:bg-brand-700 transition">
                Valider les dates
            </button>
        </form>
    </div>

<?php elseif ($cart->isEmpty()): ?>
    <!-- Panier vide (avec dates) -->
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 text-sm text-green-800">
        📅 <?= (new \Rore\Domain\Shared\ValueObject\DateRange($cart->getStartDate(), $cart->getEndDate()))->label() ?>
    </div>
    <div class="text-center py-16 text-gray-400">
        <p class="text-lg mb-4">Votre panier est vide.</p>
        <a href="/" class="text-brand-600 hover:underline">Parcourir le catalogue</a>
    </div>

<?php else: ?>
    <!-- Dates sélectionnées -->
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 flex items-center justify-between">
        <span class="text-sm text-green-800">📅 <?= (new \Rore\Domain\Shared\ValueObject\DateRange($cart->getStartDate(), $cart->getEndDate()))->label() ?></span>
        <form method="post" action="<?= $urlResolver->resolve(\Rore\Presentation\Controller\Site\CartController::class . '.setDates') ?>">
            <?= require BASE_PATH . '/templates/partials/csrf.php' ?>
            <input type="hidden" name="start_date" value="">
            <input type="hidden" name="end_date" value="">
            <button type="submit" class="text-xs text-red-500 hover:underline"
                    data-confirm="Modifier les dates videra votre panier. Continuer ?">
                Modifier les dates
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Liste produits -->
        <div class="lg:col-span-2 space-y-4">
            <?php foreach ($cartProducts as $row): ?>
                <?php $p = $row['product']; $qty = $row['quantity']; ?>
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-4">
                    <?php if ($photo = $p->getMainPhoto()): ?>
                        <img src="<?= $html($photo->getPublicPath()) ?>" alt=""
                             class="w-20 h-20 object-cover rounded-lg flex-shrink-0">
                    <?php else: ?>
                        <div class="w-20 h-20 bg-gray-100 rounded-lg flex-shrink-0"></div>
                    <?php endif; ?>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-800 truncate">
                            <a href="<?= $html($urlResolver->productUrl($p, $allCategories)) ?>" class="hover:text-brand-700">
                                <?= $html($p->getName()) ?>
                            </a>
                        </h3>
                        <p class="text-sm text-gray-500">Quantité : <?= $qty ?></p>
                        <p class="text-sm text-gray-500">
                            <?= number_format($p->calculatePrice($cart->getStartDate(), $cart->getEndDate()) * $qty, 2, ',', ' ') ?> €
                        </p>
                    </div>
                    <form method="post" action="<?= $urlResolver->resolve(\Rore\Presentation\Controller\Site\CartController::class . '.remove') ?>">
                        <?= require BASE_PATH . '/templates/partials/csrf.php' ?>
                        <input type="hidden" name="product_id" value="<?= $p->getId() ?>">
                        <button type="submit" class="text-red-400 hover:text-red-600 text-sm transition"
                                data-confirm="Retirer ce produit du panier ?">
                            ✕
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Récap -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 h-fit">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Récapitulatif</h2>
            <?php
            $total   = 0;
            $nbJours = (new \Rore\Domain\Shared\ValueObject\DateRange($cart->getStartDate(), $cart->getEndDate()))->nbDays();
            foreach ($cartProducts as $row) {
                $total += $row['product']->calculatePrice($cart->getStartDate(), $cart->getEndDate()) * $row['quantity'];
            }
            ?>
            <div class="flex justify-between text-sm text-gray-600 mb-2">
                <span>Durée</span>
                <span><?= $nbJours ?> jour(s)</span>
            </div>
            <div class="flex justify-between text-sm text-gray-600 mb-4">
                <span>Total estimé</span>
                <span class="font-bold text-gray-900"><?= number_format($total, 2, ',', ' ') ?> €</span>
            </div>
            <a href="<?= $urlResolver->resolve(\Rore\Presentation\Controller\Site\CartController::class . '.checkout') ?>"
               class="block w-full bg-brand-600 text-white text-center font-semibold py-3 rounded-xl hover:bg-brand-700 transition">
                Réserver →
            </a>
            <p class="text-xs text-gray-400 mt-3 text-center">
                <?= $html($settings->get('cart.footer_note')) ?>
            </p>
        </div>
    </div>
<?php endif; ?>
