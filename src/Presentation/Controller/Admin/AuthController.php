<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Presentation\Controller\Controller;

use Rore\Framework\Http\Route;
class AuthController extends Controller
{
    #[Route('GET', '/admin')]
    public function login(): void
    {
        if (!empty($this->session->get('admin_logged_in'))) {
            $this->redirect($this->urlResolver->resolve(DashboardController::class . '.index'));
        }
        $this->render('admin/login', ['title' => 'Administration'], 'layout/admin');
    }

    #[Route('POST', '/admin/connexion')]
    public function processLogin(): void
    {
        $this->requirePost();

        $password = $this->request->body->getString('password');
        $expected = (string) $this->config->getString('admin.password', '');

        if ($password === $expected) {
            $this->session->set('admin_logged_in', true);
            $this->redirect($this->urlResolver->resolve(DashboardController::class . '.index'));
        }

        $this->flash('error', 'Mot de passe incorrect.');
        $this->redirect($this->urlResolver->resolve(AuthController::class . '.login'));
    }

    #[Route('POST', '/admin/deconnexion')]
    public function logout(): void
    {
        $this->session->remove('admin_logged_in');
        $this->redirect($this->urlResolver->resolve(AuthController::class . '.login'));
    }
}
