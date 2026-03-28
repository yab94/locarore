<?php

declare(strict_types=1);

namespace RRB\Http;

use RRB\Bootstrap\Config;
use RRB\Security\CsrfTokenManager;
use RRB\Di\BindAdapter;
use RRB\Session\PhpSession;
use RRB\Session\SessionInterface;
use RRB\View\HtmlEncoder;
use RRB\View\PageMeta;
use RRB\View\Template;

abstract class Controller
{
    public function __construct(
        readonly HttpRequest $request,
        readonly HttpResponse $response,
        readonly Config $config,
        readonly HtmlEncoder $html,
        readonly UrlResolver $urlResolver,
        #[BindAdapter(PhpSession::class)]
        private readonly SessionInterface $session,
        readonly CsrfTokenManager $csrfTokenManager,
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
            'url'         => $this->urlResolver,
            'csrfToken'   => $this->csrfTokenManager->token(),
            'flash'       => $this->getFlash(),
            'meta'        => $data['meta'] ?? new PageMeta(title: $this->config->getString('app.name')),
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
