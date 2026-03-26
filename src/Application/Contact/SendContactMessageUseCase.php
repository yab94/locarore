<?php

declare(strict_types=1);

namespace Rore\Application\Contact;

use Rore\Framework\MailerInterface;
use Rore\Application\Settings\GetSettingUseCase;
use Rore\Domain\Contact\Entity\ContactMessage;
use Rore\Domain\Contact\Repository\ContactMessageRepositoryInterface;

final class SendContactMessageUseCase
{
    public function __construct(
        private readonly ContactMessageRepositoryInterface $repo,
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
