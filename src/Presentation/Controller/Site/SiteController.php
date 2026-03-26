<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Domain\Cart\Service\CartService;
use Rore\Application\Catalog\GetAllActiveCategoriesUseCase;
use Rore\Domain\Shared\ValueObject\DateRange;
use Rore\Presentation\Controller\Controller;

/**
 * Base pour tous les contrôleurs du site public.
 * Ajoute le panier et les catégories (header nav) aux données de rendu.
 */
abstract class SiteController extends Controller
{
    public function __construct(
        readonly CartService                    $cart,
        readonly GetAllActiveCategoriesUseCase $getActiveCategories,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    protected function render(
        string $template,
        array  $data   = [],
        ?string $layout = null
    ): void {
        $data['cartItemCount']    = $this->cart->getItemCount();
        $data['cart']             = $this->cart;
        $data['cartDateRange']    = $this->cart->hasDates()
            ? new DateRange($this->cart->getStartDate(), $this->cart->getEndDate())
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
