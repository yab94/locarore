<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use RRB\Http\Route;
use Rore\Application\Auth\UseCase\AuthenticateAdminUseCase;
use Rore\Application\Auth\UseCase\IsAdminAuthenticatedUseCase;
use Rore\Application\Auth\UseCase\LogoutAdminUseCase;
use Rore\Presentation\Controller\Controller;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthenticateAdminUseCase    $authenticateAdmin,
        private readonly LogoutAdminUseCase          $logoutAdmin,
        private readonly IsAdminAuthenticatedUseCase $isAdminAuthenticated,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    #[Route('GET', '/admin')]
    public function login(): void
    {
        if ($this->isAdminAuthenticated->execute()) {
            $this->redirect($this->urlResolver->resolve(DashboardController::class . '.index'));
        }
        $this->render('admin/login', ['title' => 'Administration'], 'layout/admin-login');
    }

    #[Route('POST', '/admin/connexion')]
    public function processLogin(): void
    {
        $this->requirePost();

        $password = $this->request->body->getString('password');
        $result   = $this->authenticateAdmin->execute($password);

        if ($result['success']) {
            $this->redirect($this->urlResolver->resolve(DashboardController::class . '.index'));
        }

        $this->flash('error', $result['error'] ?? 'Erreur inconnue.');
        $this->redirect($this->urlResolver->resolve(AuthController::class . '.login'));
    }

    #[Route('POST', '/admin/deconnexion')]
    public function logout(): void
    {
        $this->logoutAdmin->execute();
        $this->redirect($this->urlResolver->resolve(AuthController::class . '.login'));
    }
}
