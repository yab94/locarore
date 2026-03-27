<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Framework\Http\Route;
use Rore\Presentation\Controller\Controller;
use Rore\Presentation\Security\LoginRateLimiter;

class AuthController extends Controller
{
    public function __construct(
        private readonly LoginRateLimiter $rateLimiter,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    #[Route('GET', '/admin')]
    public function login(): void
    {
        if (!empty($this->session->get('admin_logged_in'))) {
            $this->redirect($this->urlResolver->resolve(DashboardController::class . '.index'));
        }
        $this->render('admin/login', ['title' => 'Administration'], 'layout/admin-login');
    }

    #[Route('POST', '/admin/connexion')]
    public function processLogin(): void
    {
        $this->requirePost();

        if ($this->rateLimiter->isLocked()) {
            $minutes = (int) ceil($this->rateLimiter->secondsUntilUnlock() / 60);
            $this->flash('error', "Trop de tentatives. Réessayez dans $minutes min.");
            $this->redirect($this->urlResolver->resolve(AuthController::class . '.login'));
        }

        $password = $this->request->body->getString('password');
        $expected = (string) $this->config->getString('admin.password', '');

        if ($password === $expected) {
            $this->rateLimiter->reset();
            $this->session->set('admin_logged_in', true);
            $this->redirect($this->urlResolver->resolve(DashboardController::class . '.index'));
        }

        $this->rateLimiter->hit();

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
