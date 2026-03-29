<?php

declare(strict_types=1);

namespace RRB\Http;


abstract class Controller
{
    public function __construct(
        readonly HttpRequest $request,
        readonly HttpResponse $response,
    ) {}

    protected function redirect(string $url): never
    {
        $this->response->redirect($url);
        exit;
    }
}
