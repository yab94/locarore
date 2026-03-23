<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Infrastructure\Config\SettingsStore;
use Rore\Presentation\Controller\Controller;

abstract class AdminController extends Controller
{
    public function __construct(
        SessionStorageInterface $session,
        CsrfTokenManagerInterface $csrfTokenManager,
        SettingsStore           $settings,
    ) {
        parent::__construct($session, $csrfTokenManager, $settings);
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
