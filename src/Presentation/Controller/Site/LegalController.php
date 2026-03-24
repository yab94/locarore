<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

class LegalController extends SiteController
{
    public function mentions(): void
    {
        $this->render('site/legal', [
            'title' => 'Mentions légales — ' . $this->settings->get('site.name'),
        ]);
    }
}
