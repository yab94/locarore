<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Cart\CartSession;
use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Settings\SettingsServiceInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Infrastructure\Config\Config;
use Rore\Presentation\Controller\Controller;
use Rore\Presentation\Http\RequestInterface;
use Rore\Presentation\Http\ResponseInterface;
use Rore\Presentation\Seo\UrlResolver;

abstract class AdminController extends Controller
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        Config $config,
        SessionStorageInterface $session,
        CsrfTokenManagerInterface $csrfTokenManager,
        SettingsServiceInterface $settings,
        CartSession $cart,
        UrlResolver $urlResolver,
    ) {
        parent::__construct($request, $response, $config, $session, $csrfTokenManager, $settings, $cart, $urlResolver);
        if (empty($this->session->get('admin_logged_in'))) {
            $this->redirect('/admin');
        }
    }

    protected function render(
        string $template,
        array  $data   = [],
        string $layout = 'layout/admin'
    ): void {
        parent::render($template, $data, $layout);
    }
}
