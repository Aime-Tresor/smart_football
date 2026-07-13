<?php

namespace App\Services;

/**
 * Thin wrapper around the existing fa_user/controls/mail.php::send_mail()
 * helper (PHPMailer/Gmail SMTP already configured there) so new features
 * don't duplicate SMTP setup. Never throws - a failed send is reported via
 * the boolean return so callers can log it without blocking the caller's
 * own transaction (e.g. a card save).
 */
class MailService
{
    private static bool $loaded = false;

    public function send(string $recipientEmail, string $subject, string $htmlMessage): bool
    {
        if ($recipientEmail === '') {
            return false;
        }

        self::loadSender();

        try {
            return (bool) \send_mail($recipientEmail, $subject, $htmlMessage);
        } catch (\Throwable $e) {
            error_log('MailService: send failed to ' . $recipientEmail . ': ' . $e->getMessage());
            return false;
        }
    }

    private static function loadSender(): void
    {
        if (self::$loaded) {
            return;
        }
        self::$loaded = true;
        if (!function_exists('send_mail')) {
            require dirname(__DIR__, 3) . '/fa_user/controls/mail.php';
        }
    }
}
