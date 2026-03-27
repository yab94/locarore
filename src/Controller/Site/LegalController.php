<?php

declare(strict_types=1);

namespace Rore\Controller\Site;

use RRB\Http\Route;
class LegalController extends SiteController
{
    #[Route('GET', '/mentions-legales')]
    public function mentions(): void
    {
        $this->render('site/legal', [
            'title' => 'Mentions légales — ' . $this->settings->get('site.name'),
        ]);
    }
}
