<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller;

use Rore\Application\Settings\GetSettingUseCase;
use Rore\Framework\From;
use Rore\Framework\Config;
use Rore\Presentation\Seo\SlugResolver;

abstract class Controller extends \Rore\Framework\Controller
{
    public function __construct(
        readonly GetSettingUseCase $settings,
        #[From(static function(Config $config) { return [
            'siteUrl' => $config->getString('seo.site_url'),
            'categoriesBaseUrl' => $config->getString('seo.categories_base_url'),
            'productsBaseUrl' => $config->getString('seo.products_base_url'),
            'tagsBaseUrl' => $config->getString('seo.tags_base_url'),
        ]; })]
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
