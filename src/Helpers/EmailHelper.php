<?php

namespace Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailHelper
{
    private static ?PHPMailer $mailer = null;

    private static function getMailer(): PHPMailer
    {
        if (self::$mailer === null) {
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

            self::$mailer = $mail;
        }

        return self::$mailer;
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
            error_log("EmailHelper Error: " . $mail->ErrorInfo ?? $e->getMessage());
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
}
