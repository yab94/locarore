<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Mail;

use RRB\Di\BindConfig;
use RRB\Mail\SmtpMailer;
use Rore\Application\Contact\Port\MailerInterface;

final class SmtpMailerAdapter extends SmtpMailer implements MailerInterface
{
    public function __construct(
        #[BindConfig('smtp.host')]
        string $host,
        #[BindConfig('smtp.port')]
        int    $port       = 587,
        #[BindConfig('smtp.encryption')]
        string $encryption = 'tls',
        #[BindConfig('smtp.user')]
        string $user       = '',
        #[BindConfig('smtp.password')]
        string $password   = '',
        #[BindConfig('smtp.from_email')]
        string $fromEmail  = '',
        #[BindConfig('smtp.from_name')]
        string $fromName   = '',
    ) {
        parent::__construct($host, $port, strtolower($encryption), $user, $password, $fromEmail, $fromName ?: $fromEmail);
    }
}

