<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Mail;

use RRB\Mail\SmtpMailer;
use Rore\Application\Contact\Port\MailerInterface;

final class SmtpMailerAdapter implements MailerInterface
{
    public function __construct(private readonly SmtpMailer $mailer) {}

    public function send(
        string  $to,
        string  $subject,
        string  $body,
        ?string $replyTo = null,
        bool    $isHtml  = false,
    ): void {
        $this->mailer->send($to, $subject, $body, $replyTo, $isHtml);
    }
}
