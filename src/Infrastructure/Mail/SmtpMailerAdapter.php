<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Mail;

use RRB\Mail\SmtpMailer;
use Rore\Application\Contact\Port\MailerInterface;

final class SmtpMailerAdapter extends SmtpMailer implements MailerInterface
{
    public function __construct(
        string $host,
        int    $port       = 587,
        string $encryption = 'tls',
        string $user       = '',
        string $password   = '',
        string $fromEmail  = '',
        string $fromName   = '',
    ) {
        parent::__construct($host, $port, strtolower($encryption), $user, $password, $fromEmail, $fromName ?: $fromEmail);
    }
}

