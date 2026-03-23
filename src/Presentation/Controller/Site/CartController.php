<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Cart\AddToCartUseCase;
use Rore\Application\Cart\CartSession;
use Rore\Application\Cart\CheckoutUseCase;
use Rore\Application\Cart\RemoveFromCartUseCase;
use Rore\Application\Cart\SetCartDatesUseCase;
use Rore\Application\Reservation\CreateReservationUseCase;
use Rore\Domain\Reservation\Service\AvailabilityService;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Infrastructure\Persistence\MySqlReservationRepository;
use Rore\Presentation\Controller\Controller;
use Rore\Presentation\Seo\PageMetaBuilder;

class CartController extends Controller
{
    public function __construct(
        private readonly CartSession                $cart,
        private readonly MySqlProductRepository     $productRepo,
        private readonly MySqlCategoryRepository    $categoryRepo,
        private readonly MySqlReservationRepository $reservationRepo,
        private readonly PageMetaBuilder            $metaBuilder,
    ) {}

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
        $redirect = $_POST['redirect'] ?? '/panier';

        $startDate = trim($_POST['start_date'] ?? '');
        $endDate   = trim($_POST['end_date']   ?? '');

        // Dates vides = intention de réinitialiser le panier
        if ($startDate === '' || $endDate === '') {
            $this->cart->clear();
            $this->redirect('/panier');
            return;
        }

        try {
            (new SetCartDatesUseCase($this->cart))->execute(
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
            $availability = new AvailabilityService($this->reservationRepo);

            (new AddToCartUseCase($this->cart, $this->productRepo, $availability))->execute(
                productId: (int) ($_POST['product_id'] ?? 0),
                quantity:  (int) ($_POST['quantity']   ?? 1),
            );
            $this->flash('success', 'Produit ajouté au panier.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }

        $redirect = $_POST['redirect'] ?? '/panier';
        $this->redirect($redirect);
    }

    public function remove(): void
    {
        $this->requirePost();
        (new RemoveFromCartUseCase($this->cart))->execute((int) ($_POST['product_id'] ?? 0));
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
            $useCase = new CheckoutUseCase(
                $this->cart,
                new CreateReservationUseCase($this->reservationRepo),
            );

            $reservationId = $useCase->execute(
                customerName:    trim($_POST['customer_name']    ?? ''),
                customerEmail:   trim($_POST['customer_email']   ?? ''),
                customerPhone:   trim($_POST['customer_phone']   ?? '') ?: null,
                customerAddress: trim($_POST['customer_address'] ?? '') ?: null,
                eventAddress:    trim($_POST['event_address']    ?? '') ?: null,
                notes:           trim($_POST['notes']            ?? '') ?: null,
            );

            $this->redirect('/panier/confirmation?id=' . $reservationId);
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect('/panier/checkout');
        }
    }

    public function confirmation(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $this->render('site/confirmation', [
            'meta'          => $this->metaBuilder->forConfirmation(),
            'reservationId' => $id,
        ]);
    }
}
