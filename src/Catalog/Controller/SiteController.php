<?php

declare(strict_types=1);

namespace Rore\Catalog\Controller;

use Rore\Cart\ValueObject\CartState;
use Rore\Cart\UseCase\GetCartStateUseCase;
use Rore\Catalog\UseCase\GetAllActiveCategoriesUseCase;
use Rore\Shared\ValueObject\DateRange;
use Rore\Shared\Controller\Controller;

/**
 * Base pour tous les contrôleurs du site public.
 * Ajoute le panier et les catégories (header nav) aux données de rendu.
 */
abstract class SiteController extends Controller
{
    private ?CartState $cartStateCache = null;

    public function __construct(
        readonly GetCartStateUseCase           $getCartState,
        readonly GetAllActiveCategoriesUseCase $getActiveCategories,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    protected function cartState(): CartState
    {
        return $this->cartStateCache ??= $this->getCartState->execute();
    }

    protected function render(
        string $template,
        array  $data   = [],
        ?string $layout = null
    ): void {
        $cart = $this->cartState();
        $data['cartItemCount']    = $cart->getItemCount();
        $data['cart']             = $cart;
        $data['cartDateRange']    = $cart->hasDates()
            ? new DateRange($cart->getStartDate(), $cart->getEndDate())
            : null;
        $data['headerCategories'] = $this->getActiveCategories->execute();
        parent::render($template, $data, $layout ?? 'layout/site');
    }

    /** @return array{url: string, w: int, h: int} */
    protected function defaultOgImage(): array
    {
        return [
            'url' => $this->slugResolver->siteUrl() . '/assets/images/og-default.jpg',
            'w'   => 1200,
            'h'   => 630,
        ];
    }
}
