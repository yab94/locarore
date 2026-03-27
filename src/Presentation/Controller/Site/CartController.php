<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Cart\UseCase\AddToCartUseCase;
use Rore\Application\Cart\UseCase\AddPackToCartUseCase;
use Rore\Application\Cart\UseCase\GetCartDataUseCase;
use Rore\Application\Cart\UseCase\CheckoutUseCase;
use Rore\Application\Cart\UseCase\RemoveFromCartUseCase;
use Rore\Application\Cart\UseCase\RemovePackFromCartUseCase;
use Rore\Application\Cart\UseCase\SetCartDatesUseCase;
use Rore\Domain\Cart\Service\CartService;
use RRB\Http\Route;
use RRB\View\PageMeta;

class CartController extends SiteController
{
    public function __construct(
        private readonly CartService                 $cartService,
        private readonly GetCartDataUseCase          $getCartDataUseCase,
        private readonly SetCartDatesUseCase         $setCartDatesUseCase,
        private readonly AddToCartUseCase            $addToCartUseCase,
        private readonly AddPackToCartUseCase        $addPackToCartUseCase,
        private readonly RemoveFromCartUseCase       $removeFromCartUseCase,
        private readonly RemovePackFromCartUseCase   $removePackFromCartUseCase,
        private readonly CheckoutUseCase             $checkoutUseCase,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    #[Route('GET', '/panier')]
    public function index(): void
    {
        $cart      = $this->cartState();
        $startDate = $cart->hasDates() ? new \DateTimeImmutable($cart->getStartDate()) : null;
        $endDate   = $cart->hasDates() ? new \DateTimeImmutable($cart->getEndDate())   : null;

        $data = $this->getCartDataUseCase->execute(
            $cart->getItems(),
            $cart->getPacks(),
            $startDate,
            $endDate
        );

        $this->render('site/cart', [
            'meta' => (function() {
                $meta = new PageMeta(
                    robots: 'noindex, follow',
                    title: ['Mon panier', $this->settings->get('site.name')],
                );
                return $meta;
            })(),
            'cartProducts'  => $data['cartProducts'],
            'cartPacks'     => $data['cartPacks'],
            'productPrices' => $data['productPrices'],
            'packPrices'    => $data['packPrices'],
            'allCategories' => $data['allCategories'],
        ]);
    }

    #[Route('POST', '/panier/dates')]
    public function setDates(): void
    {
        $this->requirePost();
        $redirect = $this->request->body->getString('redirect', $this->urlResolver->resolve(self::class . '.index'));

        $startDate = $this->request->body->getString('start_date');
        $endDate   = $this->request->body->getString('end_date');

        // Dates vides = intention de réinitialiser le panier
        if ($startDate === '' || $endDate === '') {
            $this->cartService->clear();
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

    #[Route('POST', '/panier/ajouter')]
    public function add(): void
    {
        $this->requirePost();
        try {
            $this->addToCartUseCase->execute(
                productId: $this->request->body->getInt('product_id'),
                quantity:  $this->request->body->getInt('quantity', 1),
            );
            $this->flash('success', 'Produit ajouté au panier.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }

        $redirect = $this->request->queryString->getString('redirect', $this->urlResolver->resolve(self::class . '.index'));
        $this->redirect($redirect);
    }

    #[Route('POST', '/panier/ajouter-pack')]
    public function addPack(): void
    {
        $this->requirePost();
        try {
            $selections = $this->request->body->getArray('slot_selection');
            $this->addPackToCartUseCase->execute(
                packId:     $this->request->body->getInt('pack_id'),
                selections: is_array($selections) ? $selections : [],
            );
            $this->flash('success', 'Pack ajouté au panier.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }

        $redirect = $this->request->queryString->getString('redirect', $this->urlResolver->resolve(self::class . '.index'));
        $this->redirect($redirect);
    }

    #[Route('POST', '/panier/supprimer')]
    public function remove(): void
    {
        $this->requirePost();
        $this->removeFromCartUseCase->execute($this->request->body->getInt('product_id'));
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }

    #[Route('POST', '/panier/supprimer-pack')]
    public function removePack(): void
    {
        $this->requirePost();
        $this->removePackFromCartUseCase->execute($this->request->body->getInt('pack_id'));
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }

    #[Route('GET', '/panier/checkout')]
    public function checkout(): void
    {
        $cart = $this->cartState();
        if (!$cart->hasDates() || $cart->isEmpty()) {
            $this->redirect($this->urlResolver->resolve(self::class . '.index'));
        }

        $this->render('site/checkout', [
            'meta' => (function() {
                $meta = new PageMeta(
                    robots: 'noindex, follow',
                    title: ['Finaliser ma réservation', $this->settings->get('site.name')],
                );
                return $meta;
            })(),
        ]);
    }

    #[Route('POST', '/panier/checkout')]
    public function processCheckout(): void
    {
        $this->requirePost();


        try {
            $reservationId = $this->checkoutUseCase->execute(
                customerName:    $this->request->body->getString('customer_name'),
                customerEmail:   $this->request->body->getString('customer_email'),
                customerPhone:   $this->request->body->getString('customer_phone') ?: null,
                customerAddress: $this->request->body->getString('customer_address') ?: null,
                eventAddress:    $this->request->body->getString('event_address') ?: null,
                notes:           $this->request->body->getString('notes') ?: null,
            );

            $this->redirect($this->urlResolver->resolve(self::class . '.confirmation') . '?id=' . $reservationId);
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect($this->urlResolver->resolve(self::class . '.checkout'));
        }
    }

    #[Route('GET', '/panier/confirmation')]
    public function confirmation(): void
    {
        $id = $this->request->queryString->getInt('id');
        $this->render('site/confirmation', [
            'meta' => (function() {
                $meta = new PageMeta(
                    robots: 'noindex, follow',
                    title: ['Demande envoyée', $this->settings->get('site.name')],
                );
                return $meta;
            })(),
            'reservationId' => $id,
        ]);
    }
}
