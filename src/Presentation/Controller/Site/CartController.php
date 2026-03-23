<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Cart\AddToCartUseCase;
use Rore\Application\Cart\CartSession;
use Rore\Application\Cart\CheckoutUseCase;
use Rore\Application\Cart\RemoveFromCartUseCase;
use Rore\Application\Cart\SetCartDatesUseCase;
use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Infrastructure\Config\SettingsStore;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Presentation\Controller\Controller;
use Rore\Presentation\Http\RequestInterface;
use Rore\Presentation\Http\ResponseInterface;
use Rore\Presentation\Seo\PageMetaBuilder;

class CartController extends Controller
{
    public function __construct(
        private readonly CartSession             $cart,
        private readonly MySqlProductRepository $productRepo,
        private readonly MySqlCategoryRepository $categoryRepo,
        private readonly PageMetaBuilder         $metaBuilder,
        private readonly SetCartDatesUseCase     $setCartDatesUseCase,
        private readonly AddToCartUseCase        $addToCartUseCase,
        private readonly RemoveFromCartUseCase   $removeFromCartUseCase,
        private readonly CheckoutUseCase         $checkoutUseCase,
        RequestInterface                         $request,
        ResponseInterface                        $response,
        SessionStorageInterface                  $session,
        CsrfTokenManagerInterface                $csrfTokenManager,
        SettingsStore                            $settings,
    ) {
        parent::__construct($request, $response, $session, $csrfTokenManager, $settings);
    }

    public function index(): void
    {
        $cartItems    = $this->cart->getItems();
        $cartProducts = [];

        foreach ($cartItems as $productId => $quantity) {
            $product = $this->productRepo->findById((int) $productId);
            if ($product) {
                $cartProducts[] = ['product' => $product, 'quantity' => $quantity];
            }
        }

        $allCategories = $this->categoryRepo->findAllActive();

        $this->render('site/cart', [
            'meta'          => $this->metaBuilder->forCart(),
            'cart'          => $this->cart,
            'cartProducts'  => $cartProducts,
            'allCategories' => $allCategories,
        ]);
    }

    public function setDates(): void
    {
        $this->requirePost();
        $redirect = $this->inputString('redirect', '/panier');

        $startDate = $this->inputString('start_date');
        $endDate   = $this->inputString('end_date');

        // Dates vides = intention de réinitialiser le panier
        if ($startDate === '' || $endDate === '') {
            $this->cart->clear();
            $this->redirect('/panier');
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
            $redirect = '/panier';
        }
        $this->redirect($redirect);
    }

    public function add(): void
    {
        $this->requirePost();
        try {
            $this->addToCartUseCase->execute(
                productId: $this->inputInt('product_id'),
                quantity:  $this->inputInt('quantity', 1),
            );
            $this->flash('success', 'Produit ajouté au panier.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }

        $redirect = $this->inputString('redirect', '/panier');
        $this->redirect($redirect);
    }

    public function remove(): void
    {
        $this->requirePost();
        $this->removeFromCartUseCase->execute($this->inputInt('product_id'));
        $this->redirect('/panier');
    }

    public function checkout(): void
    {
        if (!$this->cart->hasDates() || $this->cart->isEmpty()) {
            $this->redirect('/panier');
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
                customerName:    $this->inputString('customer_name'),
                customerEmail:   $this->inputString('customer_email'),
                customerPhone:   $this->inputStringOrNull('customer_phone'),
                customerAddress: $this->inputStringOrNull('customer_address'),
                eventAddress:    $this->inputStringOrNull('event_address'),
                notes:           $this->inputStringOrNull('notes'),
            );

            $this->redirect('/panier/confirmation?id=' . $reservationId);
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect('/panier/checkout');
        }
    }

    public function confirmation(): void
    {
        $id = $this->inputInt('id');
        $this->render('site/confirmation', [
            'meta'          => $this->metaBuilder->forConfirmation(),
            'reservationId' => $id,
        ]);
    }
}
