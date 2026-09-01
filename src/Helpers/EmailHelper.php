<?php

namespace Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailHelper
{
    private static function getMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST');
        $mail->Port = (int) ($_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?? 587);
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USERNAME'] ?? getenv('SMTP_USERNAME');
        $mail->Password = $_ENV['SMTP_PASSWORD'] ?? getenv('SMTP_PASSWORD');
        $mail->SMTPSecure = $_ENV['SMTP_ENCRYPTION'] ?? getenv('SMTP_ENCRYPTION') ?? PHPMailer::ENCRYPTION_STARTTLS;

        $fromEmail = $_ENV['SMTP_FROM_EMAIL'] ?? getenv('SMTP_FROM_EMAIL');
        $fromName = $_ENV['SMTP_FROM_NAME'] ?? getenv('SMTP_FROM_NAME') ?? 'EventX';
        if ($fromEmail) {
            $mail->setFrom($fromEmail, $fromName);
        }

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        return $mail;
    }

    public static function send(string $to, string $subject, string $htmlBody, ?string $altBody = null): bool
    {
        try {
            $mail = self::getMailer();
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $altBody ?? strip_tags($htmlBody);

            $mail->send();
            $mail->clearAddresses();
            return true;
        } catch (Exception $e) {
            $errorMsg = isset($mail) ? $mail->ErrorInfo : $e->getMessage();
            error_log("EmailHelper Error: " . $errorMsg);
            return false;
        }
    }

    public static function sendWithTemplate(string $to, string $subject, string $template, array $data = []): bool
    {
        $htmlBody = self::renderTemplate($template, $data);
        return self::send($to, $subject, $htmlBody);
    }

    private static function renderTemplate(string $template, array $data): string
    {
        $templatePath = __DIR__ . '/../../templates/emails/' . $template . '.html';
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Email template not found: " . $templatePath);
        }

        $html = file_get_contents($templatePath);
        foreach ($data as $key => $value) {
            $html = str_replace('{{' . $key . '}}', htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'), $html);
        }

        return $html;
    }

    private static function host(string $envKey): string
    {
        $value = $_ENV[$envKey] ?? getenv($envKey);
        if (!is_string($value) || $value === '') {
            return 'localhost';
        }
        return rtrim($value, '/');
    }

    public static function backendUrl(): string
    {
        $host = self::host('DOMAIN');
        if (str_starts_with($host, 'http://') || str_starts_with($host, 'https://')) {
            return rtrim($host, '/');
        }
        return 'http://' . $host;
    }

    public static function frontendUrl(): string
    {
        $frontendHost = $_ENV['FRONTEND_HOST'] ?? getenv('FRONTEND_HOST');
        if (is_string($frontendHost) && $frontendHost !== '') {
            return rtrim($frontendHost, '/');
        }
        return 'http://' . self::host('DOMAIN') . ':3000';
    }
}
