<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller;

abstract class Controller
{
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

    protected function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }
    }
}
