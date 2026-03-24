<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Cart\CartSession;
use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Settings\SettingsServiceInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Domain\Shared\ValueObject\DateRange;
use Rore\Infrastructure\Config\Config;
use Rore\Presentation\Controller\Controller;
use Rore\Presentation\Http\RequestInterface;
use Rore\Presentation\Http\ResponseInterface;
use Rore\Presentation\Seo\UrlResolver;
use Rore\Presentation\Template\HtmlHelper;

/**
 * Base pour tous les contrôleurs du site public.
 * Ajoute le panier et les catégories (header nav) aux données de rendu.
 */
abstract class SiteController extends Controller
{
    public function __construct(
        readonly CartSession                 $cart,
        readonly CategoryRepositoryInterface $categoryRepository,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    protected function render(
        string $template,
        array  $data   = [],
        string $layout = 'layout/base'
    ): void {
        $data['cartItemCount']    = $this->cart->getItemCount();
        $data['cart']             = $this->cart;
        $data['cartDateRange']    = $this->cart->hasDates()
            ? new DateRange($this->cart->getStartDate(), $this->cart->getEndDate())
            : null;
        $data['headerCategories'] = $this->categoryRepository->findAllActive();
        parent::render($template, $data, $layout);
    }
}
