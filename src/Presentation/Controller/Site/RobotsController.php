<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use RRB\Http\Route;

class RobotsController extends SiteController
{
    public function __construct(...$parentDeps)
    {
        parent::__construct(...$parentDeps);
    }

    #[Route('GET', '/robots.txt')]
    public function index(): void
    {
        $this->response->header('Content-Type', 'text/plain; charset=UTF-8');

        $this->render('site/robots', [
            'siteUrl' => $this->slugResolver->siteUrl(),
        ], '');
    }
}
