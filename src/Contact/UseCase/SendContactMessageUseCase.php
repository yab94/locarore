<?php

declare(strict_types=1);

namespace Rore\Contact\UseCase;

use Rore\Framework\Bootstrap\Config;
use Rore\Framework\Di\Bind;
use Rore\Framework\Mail\SmtpMailer;
use Rore\Settings\UseCase\GetSettingUseCase;
use Rore\Contact\Entity\ContactMessage;
use Rore\Contact\Port\ContactMessageRepositoryInterface;
use Rore\Contact\Adapter\MySqlContactMessageRepository;
use Rore\Framework\Di\BindAdapter;

final class SendContactMessageUseCase
{
    public function __construct(
        #[BindAdapter(MySqlContactMessageRepository::class)]
        private readonly ContactMessageRepositoryInterface $repo,
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
        private readonly SmtpMailer                        $mailer,
        private readonly GetSettingUseCase                 $setting,
    ) {}

    public function execute(
        string  $firstName,
        string  $lastName,
        string  $email,
        ?string $phone,
        string  $subject,
        string  $content,
    ): void {
        $message = new ContactMessage(
            id:        null,
            firstName: $firstName,
            lastName:  $lastName,
            email:     $email,
            phone:     $phone,
            subject:   $subject,
            content:   $content,
            isRead:    false,
            createdAt: new \DateTimeImmutable(),
        );

        $this->repo->save($message);

        $emailTo      = $this->setting->get('contact.email_to');
        $subjectPrefix = $this->setting->get('contact.subject_prefix');
        $fullSubject  = $subjectPrefix !== '' ? "[{$subjectPrefix}] {$subject}" : $subject;

        if ($emailTo !== '') {
            $body = $this->buildEmailBody($message);
            $this->mailer->send(
                to:      $emailTo,
                subject: $fullSubject,
                body:    $body,
                replyTo: $email,
                isHtml:  true,
            );
        }
    }

    private function buildEmailBody(ContactMessage $m): string
    {
        $name    = htmlspecialchars($m->getFullName());
        $email   = htmlspecialchars($m->getEmail());
        $phone   = $m->getPhone() ? htmlspecialchars($m->getPhone()) : '—';
        $subject = htmlspecialchars($m->getSubject());
        $content = nl2br(htmlspecialchars($m->getContent()));

        return <<<HTML
        <p><strong>De :</strong> {$name} &lt;{$email}&gt;</p>
        <p><strong>Téléphone :</strong> {$phone}</p>
        <p><strong>Objet :</strong> {$subject}</p>
        <hr>
        <p>{$content}</p>
        HTML;
    }
}
