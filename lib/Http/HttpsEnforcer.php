<?php

declare(strict_types=1);

namespace RRB\Http;

/**
 * Force la redirection HTTPS (301) si la requête arrive en HTTP.
 *
 * Lit HTTP_X_FORWARDED_PROTO en priorité (reverse proxy nginx → PHP FastCGI),
 * puis REQUEST_SCHEME en fallback.
 */
final class HttpsEnforcer
{
    public function __construct(
        private readonly HttpRequest  $request,
        private readonly HttpResponse $response,
    ) {}

    public function enforce(): void
    {
        $proto = $this->request->server->getString('HTTP_X_FORWARDED_PROTO')
              ?: $this->request->server->getString('REQUEST_SCHEME');

        if ($proto !== '' && $proto !== 'https') {
            $this->response->redirect(
                'https://' . $this->request->server->getString('HTTP_HOST')
                           . $this->request->server->getString('REQUEST_URI'),
                301
            );
            exit;
        }
    }
}
