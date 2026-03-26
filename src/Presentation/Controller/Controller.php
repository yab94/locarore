<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller;

use Rore\Application\Settings\GetSettingUseCase;
use Rore\Presentation\Seo\SlugResolver;

abstract class Controller extends \Rore\Framework\Http\Controller
{
    public function __construct(
        readonly GetSettingUseCase $settings,
        readonly SlugResolver $slugResolver,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    protected function render(
        string $template,
        array  $data   = [],
        ?string $layout = null
    ): void {
        parent::render($template, [
            'settings'    => $this->settings,
            'slug'    => $this->slugResolver,
            ...$data,  // Les données spécifiques ont priorité
        ], $layout);
    }
}
