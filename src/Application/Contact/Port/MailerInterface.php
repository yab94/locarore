<?php

declare(strict_types=1);

namespace Rore\Application\Contact\Port;

interface MailerInterface
{
    public function send(
        string  $to,
        string  $subject,
        string  $body,
        ?string $replyTo = null,
        bool    $isHtml  = false,
    ): void;
}
