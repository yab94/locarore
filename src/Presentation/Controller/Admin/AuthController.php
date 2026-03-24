<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Presentation\Controller\Controller;

class AuthController extends Controller
{
    public function login(): void
    {
        if (!empty($this->session->get('admin_logged_in'))) {
            $this->redirect($this->urlResolver->resolve(DashboardController::class . '.index'));
        }
        $this->render('admin/login', ['title' => 'Administration'], 'layout/admin');
    }

    public function processLogin(): void
    {
        $this->requirePost();

        $password = $this->request->body->getStringParam('password');
        $expected = (string) $this->config->getParam('admin.password', '');

        if ($password === $expected) {
            $this->session->set('admin_logged_in', true);
            $this->redirect($this->urlResolver->resolve(DashboardController::class . '.index'));
        }

        $this->flash('error', 'Mot de passe incorrect.');
        $this->redirect($this->urlResolver->resolve(AuthController::class . '.login'));
    }

    public function logout(): void
    {
        $this->session->remove('admin_logged_in');
        $this->redirect($this->urlResolver->resolve(AuthController::class . '.login'));
    }
}
