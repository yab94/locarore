<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Infrastructure\Config\SettingsStore;
use Rore\Presentation\Controller\Controller;
use Rore\Presentation\Http\RequestInterface;
use Rore\Presentation\Http\ResponseInterface;

abstract class AdminController extends Controller
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        SessionStorageInterface $session,
        CsrfTokenManagerInterface $csrfTokenManager,
        SettingsStore           $settings,
    ) {
        parent::__construct($request, $response, $session, $csrfTokenManager, $settings);
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
