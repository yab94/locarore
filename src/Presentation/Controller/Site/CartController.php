<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Cart\AddToCartUseCase;
use Rore\Application\Cart\AddPackToCartUseCase;
use Rore\Application\Cart\CartSession;
use Rore\Presentation\Seo\UrlResolver;
use Rore\Presentation\Template\Html;
use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Domain\Catalog\Repository\PackRepositoryInterface;
use Rore\Application\Cart\CheckoutUseCase;
use Rore\Application\Cart\RemoveFromCartUseCase;
use Rore\Application\Cart\RemovePackFromCartUseCase;
use Rore\Application\Cart\SetCartDatesUseCase;
use Rore\Domain\Catalog\Service\PricingCalculator;
use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Settings\SettingsServiceInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Infrastructure\Config\Config;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Presentation\Http\RequestInterface;
use Rore\Presentation\Http\ResponseInterface;
use Rore\Presentation\Seo\PageMetaBuilder;

class CartController extends SiteController
{
    public function __construct(
        CartSession                              $cart,
        private readonly MySqlProductRepository $productRepo,
        private readonly MySqlCategoryRepository $categoryRepo,
        private readonly PageMetaBuilder         $metaBuilder,
        private readonly SetCartDatesUseCase     $setCartDatesUseCase,
        private readonly AddToCartUseCase        $addToCartUseCase,
        private readonly AddPackToCartUseCase    $addPackToCartUseCase,
        private readonly RemoveFromCartUseCase      $removeFromCartUseCase,
        private readonly RemovePackFromCartUseCase   $removePackFromCartUseCase,
        private readonly CheckoutUseCase             $checkoutUseCase,
        private readonly PackRepositoryInterface $packRepo,
        private readonly PricingCalculator       $pricing,
        RequestInterface                         $request,
        ResponseInterface                        $response,
        Config                                   $config,
        SessionStorageInterface                  $session,
        CsrfTokenManagerInterface                $csrfTokenManager,
        SettingsServiceInterface                 $settings,
        UrlResolver                              $urlResolver,
        Html                                     $html,
        CategoryRepositoryInterface                  $categoryRepository,
    ) {
        parent::__construct($request, $response, $config, $session, $csrfTokenManager, $settings, $cart, $urlResolver, $html, $categoryRepository);
    }

    public function index(): void
    {
        $cartItems    = $this->cart->getItems();
        $cartProducts = [];
        $productPrices = [];

        foreach ($cartItems as $productId => $quantity) {
            $product = $this->productRepo->findById((int) $productId);
            if ($product) {
                $cartProducts[] = ['product' => $product, 'quantity' => $quantity];
                if ($this->cart->hasDates()) {
                    $productPrices[$product->getId()] = $this->pricing->calculate(
                        $product,
                        $this->cart->getStartDate(),
                        $this->cart->getEndDate(),
                    );
                }
            }
        }

        $cartPacks  = [];
        $packPrices = [];
        foreach ($this->cart->getPacks() as $packId => $_) {
            $pack = $this->packRepo->findById((int) $packId);
            if ($pack) {
                $cartPacks[] = $pack;
                if ($this->cart->hasDates()) {
                    $packPrices[$pack->getId()] = $this->pricing->calculate(
                        $pack,
                        $this->cart->getStartDate(),
                        $this->cart->getEndDate(),
                    );
                }
            }
        }

        $allCategories = $this->categoryRepo->findAllActive();

        $this->render('site/cart', [
            'meta'          => $this->metaBuilder->forCart(),
            'cart'          => $this->cart,
            'cartProducts'  => $cartProducts,
            'cartPacks'     => $cartPacks,
            'productPrices' => $productPrices,
            'packPrices'    => $packPrices,
            'allCategories' => $allCategories,
        ]);
    }

    public function setDates(): void
    {
        $this->requirePost();
        $redirect = $this->request->body->getStringParam('redirect', $this->urlResolver->resolve(self::class . '.index'));

        $startDate = $this->request->body->getStringParam('start_date');
        $endDate   = $this->request->body->getStringParam('end_date');

        // Dates vides = intention de réinitialiser le panier
        if ($startDate === '' || $endDate === '') {
            $this->cart->clear();
            $this->redirect($this->urlResolver->resolve(self::class . '.index'));
            return;
        }

        try {
            $this->setCartDatesUseCase->execute(
                startDate: $startDate,
                endDate:   $endDate,
            );
            $this->flash('success', 'Dates enregistrées.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
            $redirect = $this->urlResolver->resolve(self::class . '.index');
        }
        $this->redirect($redirect);
    }

    public function add(): void
    {
        $this->requirePost();
        try {
            $this->addToCartUseCase->execute(
                productId: $this->request->body->getIntParam('product_id'),
                quantity:  $this->request->body->getIntParam('quantity', 1),
            );
            $this->flash('success', 'Produit ajouté au panier.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }

        $redirect = $this->request->queryString->getStringParam('redirect', $this->urlResolver->resolve(self::class . '.index'));
        $this->redirect($redirect);
    }

    public function addPack(): void
    {
        $this->requirePost();
        try {
            $this->addPackToCartUseCase->execute(
                packId: $this->request->body->getIntParam('pack_id'),
            );
            $this->flash('success', 'Pack ajouté au panier.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }

        $redirect = $this->request->queryString->getStringParam('redirect', $this->urlResolver->resolve(self::class . '.index'));
        $this->redirect($redirect);
    }

    public function remove(): void
    {
        $this->requirePost();
        $this->removeFromCartUseCase->execute($this->request->body->getIntParam('product_id'));
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }

    public function removePack(): void
    {
        $this->requirePost();
        $this->removePackFromCartUseCase->execute($this->request->body->getIntParam('pack_id'));
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }

    public function checkout(): void
    {
        if (!$this->cart->hasDates() || $this->cart->isEmpty()) {
            $this->redirect($this->urlResolver->resolve(self::class . '.index'));
        }

        $this->render('site/checkout', [
            'meta' => $this->metaBuilder->forCheckout(),
            'cart' => $this->cart,
        ]);
    }

    public function processCheckout(): void
    {
        $this->requirePost();
        try {
            $reservationId = $this->checkoutUseCase->execute(
                customerName:    $this->request->body->getStringParam('customer_name'),
                customerEmail:   $this->request->body->getStringParam('customer_email'),
                customerPhone:   $this->request->body->getStringParam('customer_phone') ?: null,
                customerAddress: $this->request->body->getStringParam('customer_address') ?: null,
                eventAddress:    $this->request->body->getStringParam('event_address') ?: null,
                notes:           $this->request->body->getStringParam('notes') ?: null,
            );

            $this->redirect($this->urlResolver->resolve(self::class . '.confirmation') . '?id=' . $reservationId);
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect($this->urlResolver->resolve(self::class . '.checkout'));
        }
    }

    public function confirmation(): void
    {
        $id = $this->request->queryString->getIntParam('id');
        $this->render('site/confirmation', [
            'meta'          => $this->metaBuilder->forConfirmation(),
            'reservationId' => $id,
        ]);
    }
}
