<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller;

use Rore\Framework\Config;
use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Settings\GetSettingUseCase;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Framework\HttpRequest;
use Rore\Framework\HttpResponse;
use Rore\Presentation\Seo\PageMeta;
use Rore\Presentation\Seo\UrlResolver;
use Rore\Framework\HtmlHelper;
use Rore\Framework\Template;

abstract class Controller
{
    public function __construct(
        readonly HttpRequest $request,
        readonly HttpResponse $response,
        readonly Config $config,
        readonly SessionStorageInterface $session,
        readonly CsrfTokenManagerInterface $csrfTokenManager,
        readonly GetSettingUseCase $settings,
        readonly UrlResolver $urlResolver,
        readonly HtmlHelper $html,
    ) {}

    protected function render(
        string $template,
        array  $data   = [],
        string $layout = 'layout/site'
    ): void {
        // Helpers globaux injectés dans chaque template
        $shared = [
            'flash'       => $this->getFlash(),
            'meta'        => $data['meta'] ?? new PageMeta(title: $this->config->getString('app.name')),
            'csrfToken'   => $this->csrfTokenManager->token(),
            'settings'    => $this->settings,
            'config'      => $this->config,
            'urlResolver' => $this->urlResolver,
            'url'         => $this->urlResolver,
            'html'        => $this->html,
        ];

        $tpl     = new Template($template, [...$shared, ...$data]);
        $content = $tpl->render();

        echo $tpl->partial($layout, ['content' => $content]);
    }

    protected function redirect(string $url): never
    {
        $this->response->redirect($url);
        exit;
    }

    protected function flash(string $type, string $message): void
    {
        $flash = $this->session->get('flash', []);
        if (!is_array($flash)) {
            $flash = [];
        }
        $flash[$type] = $message;
        $this->session->set('flash', $flash);
    }

    protected function getFlash(): array
    {
        $flash = $this->session->get('flash', []);
        $this->session->remove('flash');
        return is_array($flash) ? $flash : [];
    }

    protected function requirePost(): void
    {
        if ($this->request->method !== 'POST') {
            $this->response->setStatusCode(405);
            $this->response->write('Method Not Allowed');
            exit;
        }
        $posted = $this->request->body->getString($this->csrfTokenManager->postKey());
        if (!$this->csrfTokenManager->validate($posted)) {
            $this->response->setStatusCode(419);
            $this->response->write('Token CSRF invalide.');
            exit;
        }
    }
}
