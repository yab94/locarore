<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Cart\AddToCartUseCase;
use Rore\Application\Cart\AddPackToCartUseCase;
use Rore\Application\Cart\GetCartDataUseCase;
use Rore\Application\Cart\CheckoutUseCase;
use Rore\Application\Cart\RemoveFromCartUseCase;
use Rore\Application\Cart\RemovePackFromCartUseCase;
use Rore\Application\Cart\SetCartDatesUseCase;
use Rore\Presentation\Seo\PageMeta;

class CartController extends SiteController
{
    public function __construct(
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

    public function index(): void
    {
        $startDate = $this->cart->hasDates() 
            ? new \DateTimeImmutable($this->cart->getStartDate()) 
            : null;
        $endDate = $this->cart->hasDates() 
            ? new \DateTimeImmutable($this->cart->getEndDate()) 
            : null;

        $data = $this->getCartDataUseCase->execute(
            $this->cart->getItems(),
            $this->cart->getPacks(),
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
            'cart'          => $this->cart,
            'cartProducts'  => $data['cartProducts'],
            'cartPacks'     => $data['cartPacks'],
            'productPrices' => $data['productPrices'],
            'packPrices'    => $data['packPrices'],
            'allCategories' => $data['allCategories'],
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
            $selections = $this->request->body->getArrayParam('slot_selection');
            $this->addPackToCartUseCase->execute(
                packId:     $this->request->body->getIntParam('pack_id'),
                selections: is_array($selections) ? $selections : [],
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
            'meta' => (function() {
                $meta = new PageMeta(
                    robots: 'noindex, follow',
                    title: ['Finaliser ma réservation', $this->settings->get('site.name')],
                );
                return $meta;
            })(),
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
