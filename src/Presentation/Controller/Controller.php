<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller;

use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Infrastructure\Config\SettingsStore;

abstract class Controller
{
    public function __construct(
        readonly SessionStorageInterface $session,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        readonly SettingsStore $settings,
    ) {}

    protected function requestMethod(): string
    {
        return (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

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
        $data['cartItemCount'] = $this->getCartItemCount();
        // Accès aux settings dans toutes les vues
        $data['settings'] = $this->settings;

        extract($data);

        ob_start();
        require BASE_PATH . '/templates/' . $template . '.php';
        $content = ob_get_clean();

        require BASE_PATH . '/templates/' . $layout . '.php';
    }

    private function getCartItemCount(): int
    {
        $cart = $this->session->get('rore_cart', []);
        if (!is_array($cart)) {
            return 0;
        }
        $items = $cart['items'] ?? [];
        if (!is_array($items)) {
            return 0;
        }

        $sum = 0;
        foreach ($items as $qty) {
            $sum += (int) $qty;
        }
        return $sum;
    }

    protected function redirect(string $url): never
    {
        header('Location: ' . $url);
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

    protected function input(string $key, mixed $default = ''): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function inputString(string $key, string $default = ''): string
    {
        return trim((string) $this->input($key, $default));
    }

    protected function inputStringOrNull(string $key): ?string
    {
        $value = $this->inputString($key, '');
        return $value !== '' ? $value : null;
    }

    protected function inputInt(string $key, int $default = 0): int
    {
        return (int) $this->input($key, $default);
    }

    protected function inputFloat(string $key, float $default = 0.0): float
    {
        return (float) $this->input($key, $default);
    }

    /** @return array<mixed> */
    protected function inputArray(string $key, array $default = []): array
    {
        $value = $this->input($key, $default);
        return is_array($value) ? $value : $default;
    }

    /** @return array<mixed>|null  $_FILES entry or null if absent/empty */
    protected function file(string $key): ?array
    {
        $f = $_FILES[$key] ?? null;
        if ($f === null) {
            return null;
        }
        // Single file upload
        if (isset($f['error']) && !is_array($f['error'])) {
            return $f['error'] !== UPLOAD_ERR_NO_FILE ? $f : null;
        }
        // Multiple files upload (name[] syntax)
        return (!empty($f['name'][0])) ? $f : null;
    }

    protected function requirePost(): void
    {
        if ($this->requestMethod() !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        $posted = $this->inputString($this->csrfTokenManager->postKey());
        if (!$this->csrfTokenManager->validate($posted)) {
            http_response_code(419);
            exit('Token CSRF invalide.');
        }
    }
}
