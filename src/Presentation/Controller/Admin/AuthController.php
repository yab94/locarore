<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Presentation\Controller\Controller;

class AuthController extends Controller
{
    public function login(): void
    {
        if (!empty($_SESSION['admin_logged_in'])) {
            $this->redirect('/admin/dashboard');
        }
        $this->render('admin/login', ['title' => 'Administration']);
    }

    public function processLogin(): void
    {
        $this->requirePost();

        $config   = parse_ini_file(BASE_PATH . '/config/app.ini', true);
        $password = $_POST['password'] ?? '';

        if ($password === $config['admin']['password']) {
            $_SESSION['admin_logged_in'] = true;
            $this->redirect('/admin/dashboard');
        }

        $this->flash('error', 'Mot de passe incorrect.');
        $this->redirect('/admin');
    }

    public function logout(): void
    {
        unset($_SESSION['admin_logged_in']);
        $this->redirect('/admin');
    }
}
