<?php

declare(strict_types=1);

namespace Rore\Application\Contact\UseCase;

use Rore\Application\Contact\Port\MailerInterface;
use Rore\Application\Settings\UseCase\GetSettingUseCase;
use Rore\Domain\Contact\Entity\ContactMessage;
use Rore\Application\Contact\Port\ContactMessageRepositoryInterface;
use Rore\Infrastructure\Mail\SmtpMailerAdapter;
use Rore\Infrastructure\Persistence\MySqlContactMessageRepositoryAdapter;
use RRB\Di\BindAdapter;

final class SendContactMessageUseCase
{
    public function __construct(
        #[BindAdapter(MySqlContactMessageRepositoryAdapter::class)]
        private readonly ContactMessageRepositoryInterface $repo,
        #[BindAdapter(SmtpMailerAdapter::class)]
        private readonly MailerInterface                   $mailer,
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

        if ($emailTo !== '') {
            $body = $this->buildEmailBody($message);
            $this->mailer->send(
                to:      $emailTo,
                subject: $subject,
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
