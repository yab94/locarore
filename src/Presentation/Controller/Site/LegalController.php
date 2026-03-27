<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Framework\Http\Route;
use Rore\Catalog\Controller\SiteController;

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
