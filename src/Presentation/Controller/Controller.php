<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller;

use Rore\Application\Cart\CartSession;
use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Settings\SettingsServiceInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Infrastructure\Config\Config;
use Rore\Presentation\Http\RequestInterface;
use Rore\Presentation\Http\ResponseInterface;
use Rore\Presentation\Seo\UrlResolver;
use Rore\Presentation\Template\Html;

abstract class Controller
{
    public function __construct(
        readonly RequestInterface $request,
        readonly ResponseInterface $response,
        readonly Config $config,
        readonly SessionStorageInterface $session,
        readonly CsrfTokenManagerInterface $csrfTokenManager,
        readonly SettingsServiceInterface $settings,
        readonly CartSession $cart,
        readonly UrlResolver $urlResolver,
        readonly Html $html,
        readonly CategoryRepositoryInterface $categoryRepository,
    ) {}

    protected function render(
        string $template,
        array  $data   = [],
        string $layout = 'layout/base'
    ): void {
        // Injecte les flash messages dans chaque vue
        $data['flash'] = $this->getFlash();
        // CSRF token pour les formulaires
        $data['csrfToken'] = $this->csrfTokenManager->token();
        // Compteur panier (pour le header)
        $data['cartItemCount'] = $this->cart->getItemCount();
        // Accès aux settings dans toutes les vues
        $data['settings']      = $this->settings;
        $data['config']        = $this->config;
        $data['urlResolver']      = $this->urlResolver;
        $data['url']              = $this->urlResolver; // alias court invokable : $url('Admin\Category.edit', [...])
        $data['html']             = $this->html;
        $data['headerCategories'] = $this->categoryRepository->findAllActive();

        extract($data);

        ob_start();
        require BASE_PATH . '/templates/' . $template . '.php';
        $content = ob_get_clean();

        require BASE_PATH . '/templates/' . $layout . '.php';
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
        $posted = $this->request->body->getStringParam($this->csrfTokenManager->postKey());
        if (!$this->csrfTokenManager->validate($posted)) {
            $this->response->setStatusCode(419);
            $this->response->write('Token CSRF invalide.');
            exit;
        }
    }
}
