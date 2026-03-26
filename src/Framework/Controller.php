<?php

declare(strict_types=1);

namespace Rore\Framework;

use Rore\Framework\Config;
use Rore\Framework\HttpRequest;
use Rore\Framework\HttpResponse;
use Rore\Framework\Template;

abstract class Controller
{
    public function __construct(
        readonly HttpRequest $request,
        readonly HttpResponse $response,
        readonly Config $config,
        readonly HtmlHelper $html,
        readonly UrlResolver $urlResolver,
    ) {}

    protected function render(
        string $template,
        array  $data   = [],
        ?string $layout = null
    ): void {
        // Helpers globaux injectés dans chaque template
        $shared = [
            'config'      => $this->config,
            'html'        => $this->html,
            'urlResolver' => $this->urlResolver,
            'url'         => $this->urlResolver,
        ];

        $tpl     = new Template($template, [...$shared, ...$data]);
        $content = $tpl->render();

        echo $layout ? $tpl->partial($layout, ['content' => $content]) : $content;
    }

    protected function redirect(string $url): never
    {
        $this->response->redirect($url);
        exit;
    }
}
