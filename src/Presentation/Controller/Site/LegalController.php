<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Settings\UseCase\GetSettingUseCase;
use RRB\Http\Route;
use RRB\View\PageMeta;

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
        $appName = $this->config->getString('app.name');

        $this->render('site/legal', [
            'meta' => new PageMeta(
                title:        'Mentions légales — ' . $appName,
                description:  'Mentions légales, politique de cookies et informations légales de ' . $appName . '.',
                robots:       'noindex, follow',
                canonicalUrl: $this->slugResolver->siteUrl() . $this->urlResolver->resolve('Site\Legal.mentions'),
            ),
            'content' => $this->settings->get('mentions.content'),
        ]);
    }
}
