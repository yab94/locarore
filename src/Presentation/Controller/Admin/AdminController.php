<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Auth\UseCase\IsAdminAuthenticatedUseCase;
use Rore\Presentation\Controller\Controller;

abstract class AdminController extends Controller
{
    public function __construct(
        private readonly IsAdminAuthenticatedUseCase $isAdminAuthenticated,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
        if (!$this->isAdminAuthenticated->execute()) {
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
