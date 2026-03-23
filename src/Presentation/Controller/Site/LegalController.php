<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Presentation\Controller\Controller;

class LegalController extends Controller
{
    public function mentions(): void
    {
        $this->render('site/legal', [
            'title' => 'Mentions légales — ' . $this->setting('site.name'),
        ]);
    }
}
