<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Mail;

use RRB\Bootstrap\Config;
use RRB\Di\Bind;
use RRB\Mail\SmtpMailer;
use Rore\Application\Contact\Port\MailerInterface;

final class SmtpMailerAdapter implements MailerInterface
{
    public function __construct(
        #[Bind(static function (Config $c): SmtpMailer {
            return new SmtpMailer(
                host:       $c->getString('smtp.host'),
                port:       $c->getInt('smtp.port', 587),
                encryption: strtolower($c->getString('smtp.encryption', 'tls')),
                user:       $c->getString('smtp.user'),
                password:   $c->getString('smtp.password'),
                fromEmail:  $c->getString('smtp.from_email'),
                fromName:   $c->getString('smtp.from_name', $c->getString('smtp.from_email')),
            );
        })]
        private readonly SmtpMailer $mailer,
    ) {}

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
