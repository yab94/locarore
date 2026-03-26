<?php

declare(strict_types=1);

namespace Rore\Framework\Mail;

interface MailerInterface
{
    /**
     * Envoie un email.
     *
     * @param string      $to       Adresse du destinataire
     * @param string      $subject  Objet du mail
     * @param string      $body     Corps du mail (texte brut ou HTML)
     * @param string|null $replyTo  Adresse de réponse optionnelle
     * @param bool        $isHtml   True pour envoyer en HTML
     */
    public function send(
        string  $to,
        string  $subject,
        string  $body,
        ?string $replyTo = null,
        bool    $isHtml  = false,
    ): void;
}
