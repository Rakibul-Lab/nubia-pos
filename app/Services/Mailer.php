<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

/**
 * Email delivery via PHPMailer with a safe log-only fallback.
 *
 * @package App\Services
 */
final class Mailer
{
    public static function send(string $to, string $subject, string $body, bool $html = false): bool
    {
        $cfg = config('mail');

        // If SMTP is not configured, log the message instead of sending.
        if (empty($cfg['username']) || empty($cfg['host'])) {
            $log = config('paths.logs') . '/mail.log';
            @file_put_contents(
                $log,
                sprintf("[%s] To: %s | Subject: %s\n%s\n\n", now(), $to, $subject, $body),
                FILE_APPEND
            );
            return true;
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $cfg['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $cfg['username'];
            $mail->Password   = $cfg['password'];
            $mail->SMTPSecure = $cfg['encryption'] ?: PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $cfg['port'];

            $mail->setFrom($cfg['from_addr'], $cfg['from_name']);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->isHTML($html);
            $mail->Body    = $body;
            if (!$html) {
                $mail->AltBody = $body;
            }

            $mail->send();
            return true;
        } catch (MailException $e) {
            error_log('Mailer error: ' . $mail->ErrorInfo);
            return false;
        }
    }
}
