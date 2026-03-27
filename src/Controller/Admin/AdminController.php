<?php

declare(strict_types=1);

namespace Rore\Controller\Admin;

use Rore\Controller\Controller;

abstract class AdminController extends Controller
{
    public function __construct(
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
        if (empty($this->session->get('admin_logged_in'))) {
            $this->redirect($this->urlResolver->resolve(AuthController::class . '.login'));
        }
    }

    protected function render(
        string $template,
        array  $data   = [],
        ?string $layout = null
    ): void {
        parent::render($template, $data, $layout ?? 'layout/admin');
    }
}
