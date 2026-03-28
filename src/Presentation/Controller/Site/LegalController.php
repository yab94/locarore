<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Settings\UseCase\GetSettingUseCase;
use RRB\Http\Route;

class LegalController extends SiteController
{
    public function __construct(
        readonly GetSettingUseCase $settings,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    #[Route('GET', '/mentions-legales')]
    public function mentions(): void
    {
        $this->render('site/legal', [
            'title' => 'Mentions légales — ' . $this->config->getString('app.name'),
            'content' => $this->settings->get('mentions.content'),
        ]);
    }
}
