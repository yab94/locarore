<?php

declare(strict_types=1);

namespace Rore\Framework\Mail;

use Rore\Framework\Bootstrap\Config;
use RuntimeException;

/**
 * Mailer SMTP natif PHP (fsockopen / stream_socket_client).
 *
 * Supporte :
 *   - SMTP plain (port 25)
 *   - STARTTLS (port 587)
 *   - SMTPS / SSL (port 465)
 *   - AUTH LOGIN avec user/password
 *
 * Config attendue (section [smtp] dans default.ini / .env) :
 *   smtp.host        → ex. smtp.gmail.com
 *   smtp.port        → 587 (défaut)
 *   smtp.encryption  → tls | ssl | none  (défaut : tls)
 *   smtp.user        → identifiant SMTP
 *   smtp.password    → mot de passe SMTP
 *   smtp.from_email  → adresse expéditeur
 *   smtp.from_name   → nom expéditeur
 */
final class SmtpMailer
{
    private const TIMEOUT = 15;

    public function __construct(
        private string $host,
        private int    $port,
        private string $encryption,
        private string $user,
        private string $password,
        private string $fromEmail,
        private string $fromName,
    ) {}

    public function send(
        string  $to,
        string  $subject,
        string  $body,
        ?string $replyTo = null,
        bool    $isHtml  = false,
    ): void {
        $host       = $this->host;
        $port       = $this->port;
        $encryption = strtolower($this->encryption);
        $user       = $this->user;
        $password   = $this->password;
        $fromEmail  = $this->fromEmail;
        $fromName   = $this->fromName;

        if ($host === '') {
            throw new RuntimeException('SmtpMailer : smtp.host non configuré.');
        }

        // ── Connexion ────────────────────────────────────────────────────────
        $socket = $this->connect($host, $port, $encryption);

        try {
            $this->expect($socket, 220);
            $this->cmd($socket, "EHLO {$host}", 250);

            // STARTTLS
            if ($encryption === 'tls') {
                $this->cmd($socket, 'STARTTLS', 220);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException('SmtpMailer : échec de la négociation TLS.');
                }
                $this->cmd($socket, "EHLO {$host}", 250);
            }

            // AUTH LOGIN
            if ($user !== '') {
                $this->cmd($socket, 'AUTH LOGIN', 334);
                $this->cmd($socket, base64_encode($user), 334);
                $this->cmd($socket, base64_encode($password), 235);
            }

            $this->cmd($socket, "MAIL FROM:<{$fromEmail}>", 250);
            $this->cmd($socket, "RCPT TO:<{$to}>", 250);
            $this->cmd($socket, 'DATA', 354);

            $message = $this->buildRawMessage(
                from:      $fromEmail,
                fromName:  $fromName,
                to:        $to,
                subject:   $subject,
                body:      $body,
                replyTo:   $replyTo,
                isHtml:    $isHtml,
            );

            fwrite($socket, $message . "\r\n.\r\n");
            $this->expect($socket, 250);

            $this->cmd($socket, 'QUIT', 221);
        } finally {
            fclose($socket);
        }
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /** @return resource */
    private function connect(string $host, int $port, string $encryption): mixed
    {
        $address = $encryption === 'ssl'
            ? "ssl://{$host}:{$port}"
            : "tcp://{$host}:{$port}";

        $errno  = 0;
        $errstr = '';
        $socket = stream_socket_client($address, $errno, $errstr, self::TIMEOUT);

        if ($socket === false) {
            throw new RuntimeException(
                "SmtpMailer : connexion à {$address} échouée — {$errstr} ({$errno})"
            );
        }

        stream_set_timeout($socket, self::TIMEOUT);

        return $socket;
    }

    /** @param resource $socket */
    private function cmd(mixed $socket, string $command, int $expectedCode): string
    {
        fwrite($socket, $command . "\r\n");
        return $this->expect($socket, $expectedCode);
    }

    /** @param resource $socket */
    private function expect(mixed $socket, int $expectedCode): string
    {
        $response = '';
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            // Les lignes de continuation ont un tiret après le code : "250-..."
            // La dernière ligne a un espace : "250 ..."
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        $code = (int) substr($response, 0, 3);
        if ($code !== $expectedCode) {
            throw new RuntimeException(
                "SmtpMailer : code {$code} reçu, {$expectedCode} attendu.\n{$response}"
            );
        }

        return $response;
    }

    private function buildRawMessage(
        string  $from,
        string  $fromName,
        string  $to,
        string  $subject,
        string  $body,
        ?string $replyTo,
        bool    $isHtml,
    ): string {
        $encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
        $encodedSubject  = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $contentType     = $isHtml
            ? 'text/html; charset=UTF-8'
            : 'text/plain; charset=UTF-8';
        $date = date('r');
        $messageId = '<' . uniqid('', true) . '@' . gethostname() . '>';

        $headers  = "Date: {$date}\r\n";
        $headers .= "From: {$encodedFromName} <{$from}>\r\n";
        $headers .= "To: {$to}\r\n";
        if ($replyTo !== null) {
            $headers .= "Reply-To: {$replyTo}\r\n";
        }
        $headers .= "Subject: {$encodedSubject}\r\n";
        $headers .= "Message-ID: {$messageId}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: {$contentType}\r\n";
        $headers .= "Content-Transfer-Encoding: base64\r\n";

        // Le corps en base64, découpé en lignes de 76 chars (RFC 2045)
        $encodedBody = chunk_split(base64_encode($body));

        return $headers . "\r\n" . $encodedBody;
    }
}
