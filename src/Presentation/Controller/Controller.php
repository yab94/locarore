<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller;

use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Settings\GetSettingUseCase;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Framework\PageMeta;
use Rore\Presentation\Seo\UrlResolver;

abstract class Controller extends \Rore\Framework\Controller
{
    public function __construct(
        readonly SessionStorageInterface $session,
        readonly CsrfTokenManagerInterface $csrfTokenManager,
        readonly GetSettingUseCase $settings,
        UrlResolver $urlResolver,
        ...$parentDeps
    ) {
        parent::__construct(
            ...$parentDeps,
            urlResolver: $urlResolver,
        );
    }

    protected function render(
        string $template,
        array  $data   = [],
        ?string $layout = null
    ): void {
        parent::render($template, [
            'flash'       => $this->getFlash(),
            'meta'        => $data['meta'] ?? new PageMeta(title: $this->config->getString('app.name')),
            'csrfToken'   => $this->csrfTokenManager->token(),
            'settings'    => $this->settings,
            ...$data,  // Les données spécifiques ont priorité
        ], $layout);
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
