<?php require 'partials/breadcrumb.php'; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

    <!-- Photo principale (produit principal du pack) -->
    <div>
        <?php $mainPhoto = $mainProduct?->getMainPhoto(); ?>
        <?php if ($mainPhoto): ?>
            <?php $photoAlt = $html($mainPhoto->getDescription() ?: $pack->getName()); ?>
            <div class="rounded-2xl overflow-hidden bg-gray-100">
                <img src="<?= $html($mainPhoto->getPublicPath()) ?>"
                     alt="<?= $photoAlt ?>"
                     title="<?= $photoAlt ?>"
                     class="w-full h-96 object-cover">
            </div>
        <?php else: ?>
            <div class="rounded-2xl bg-gray-100 h-96 flex items-center justify-center text-gray-400">
                Pas de photo
            </div>
        <?php endif; ?>
    </div>

    <!-- Infos pack -->
    <div>
        <div class="inline-flex items-center gap-1.5 bg-brand-50 text-brand-700 border border-brand-200 rounded-full px-3 py-1 text-xs font-medium mb-3">
            Pack
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2"><?= $html($pack->getName()) ?></h1>
        <p class="text-2xl font-semibold text-brand-600 mb-4">
            <?= number_format($pack->getPricePerDay(), 0, ',', ' ') ?> € / jour
        </p>

        <?php if ($pack->getDescription()): ?>
            <div class="text-gray-600 mb-6 leading-relaxed prose prose-sm max-w-none">
                <?= $pack->getDescription() ?>
            </div>
        <?php endif; ?>

        <!-- Contenu du pack -->
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wide">Contenu du pack</h2>
            <ul class="space-y-2">
                <?php foreach ($pack->getItems() as $item): ?>
                    <?php $p = $productsById[$item->getProductId()] ?? null; ?>
                    <li class="flex items-center justify-between text-sm">
                        <span class="text-gray-800">
                            <?php if ($p): ?>
                                <a href="<?= $html($urlResolver->productUrl($p, $allCategories)) ?>"
                                   class="hover:text-brand-600 hover:underline">
                                    <?= $html($p->getName()) ?>
                                </a>
                            <?php else: ?>
                                Produit #<?= $item->getProductId() ?>
                            <?php endif; ?>
                        </span>
                        <span class="text-gray-500 font-medium">× <?= $item->getQuantity() ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- CTA ajout panier -->
        <?php if ($cart->hasDates()): ?>
            <?php if (isset($cart->getPacks()[$pack->getId()])): ?>
                <div class="flex items-center gap-3">
                    <span class="flex-1 block text-center bg-green-100 text-green-800 font-semibold py-3 rounded-xl border border-green-200">
                        ✓ Pack dans le panier
                    </span>
                    <form method="post" action="<?= $html($urlResolver->resolve('Site\Cart.removePack')) ?>">
                        <?= require 'partials/csrf.php' ?>
                        <input type="hidden" name="pack_id" value="<?= $pack->getId() ?>">
                        <button type="submit"
                                class="text-red-400 hover:text-red-600 text-sm transition py-3 px-2"
                                data-confirm="Retirer ce pack du panier ?">✕</button>
                    </form>
                </div>
            <?php else: ?>
                <form method="post" action="<?= $html($urlResolver->resolve('Site\Cart.addPack')) ?>">
                    <?= require 'partials/csrf.php' ?>
                    <input type="hidden" name="pack_id" value="<?= $pack->getId() ?>">
                    <button type="submit"
                            class="block w-full text-center bg-brand-600 text-white font-semibold py-3 rounded-xl hover:bg-brand-700 transition">
                        Ajouter ce pack au panier
                    </button>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <a href="<?= $html($urlResolver->resolve('Site\Cart.index')) ?>"
               class="block w-full text-center bg-brand-600 text-white font-semibold py-3 rounded-xl hover:bg-brand-700 transition">
                Choisir mes dates pour réserver
            </a>
        <?php endif; ?>
        <p class="text-xs text-gray-400 mt-2 text-center">Réservation confirmée après validation de votre devis.</p>
    </div>
</div>
