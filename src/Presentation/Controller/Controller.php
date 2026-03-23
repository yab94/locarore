<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller;

use Rore\Infrastructure\Security\CsrfTokenManager;

abstract class Controller
{
    protected function requestMethod(): string
    {
        return (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    protected function sessionGet(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    protected function sessionSet(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    protected function sessionUnset(string $key): void
    {
        unset($_SESSION[$key]);
    }

    protected function render(
        string $template,
        array  $data   = [],
        string $layout = 'layout/base'
    ): void {
        // Injecte les flash messages dans chaque vue
        $data['flash'] = $this->getFlash();

        extract($data);

        ob_start();
        require BASE_PATH . '/templates/' . $template . '.php';
        $content = ob_get_clean();

        require BASE_PATH . '/templates/' . $layout . '.php';
    }

    protected function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }

    protected function getFlash(): array
    {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
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
        $value = trim((string) $this->input($key, ''));
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
        if (!CsrfTokenManager::validate()) {
            http_response_code(419);
            exit('Token CSRF invalide.');
        }
    }
}
