<?php

namespace App\Services;

use App\Models\NotificationModel;
use App\Models\UserModel;

/**
 * Notification service implementing the chosen BRD strategy:
 *
 *  - Sends real email when SMTP is configured (email.* keys in .env)
 *  - Graceful fallback: when SMTP is NOT configured (or sending fails),
 *    the message is persisted in `email_outbox` with status "logged"/"failed"
 *  - An in-app notification is ALWAYS created for the target user
 *
 * Used by FR-1.4 (escalation), FR-1.5 (rejection), FR-6.3 (low RFID
 * balance) and FR-7.1 (LTO/GSIS 30-day expiry alerts).
 */
class MailerService
{
    protected NotificationModel $notificationModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
        $this->userModel         = new UserModel();
    }

    /**
     * Notify a user by in-app notification and (best effort) by email.
     *
     * @param int      $userId      Target user id.
     * @param string   $title       Notification title / email subject.
     * @param string   $message     Message body (plain text).
     * @param string   $type        in-app type: info|warning|danger|success.
     * @param string|null $link     In-app deep link.
     * @param bool     $sendEmail   Whether to also attempt email delivery.
     * @return array{notification: bool, email: string} email = sent|logged|failed|skipped
     */
    public function notify(
        int $userId,
        string $title,
        string $message,
        string $type = 'info',
        ?string $link = null,
        bool $sendEmail = true
    ): array {
        // 1. In-app notification (always)
        $notificationOk = (bool) $this->notificationModel->insert([
            'user_id'    => $userId,
            'title'      => $title,
            'message'    => $message,
            'type'       => $type,
            'link'       => $link,
            'is_read'    => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // 2. Email (best effort with graceful fallback)
        $emailStatus = 'skipped';
        if ($sendEmail) {
            $user = $this->userModel->find($userId);
            $emailStatus = $this->send($user['email'] ?? null, $title, $message, $userId);
        }

        return ['notification' => $notificationOk, 'email' => $emailStatus];
    }

    /**
     * Attempt SMTP delivery; fall back to logging in email_outbox.
     *
     * @return string sent|logged|failed
     */
    public function send(?string $toEmail, string $subject, string $body, ?int $toUserId = null): string
    {
        if (empty($toEmail)) {
            return $this->outbox(null, $toUserId, $subject, $body, 'failed', 'No recipient email address.');
        }

        // SMTP settings come from Config\Email, which is automatically
        // overridable through `email.*` keys in .env (e.g. email.smtphost).
        $emailConfig = config('Email');
        $smtpHost    = trim((string) ($emailConfig->SMTPHost ?? ''));

        // Graceful fallback: SMTP not configured → log the email instead of failing
        if ($smtpHost === '') {
            return $this->outbox($toEmail, $toUserId, $subject, $body, 'logged', 'SMTP not configured — message logged.');
        }

        try {
            $email = service('email');
            $fromEmail = trim((string) $emailConfig->fromEmail) ?: 'noreply@pia.gov.ph';
            $fromName  = trim((string) $emailConfig->fromName) ?: 'PIA-AFMS Fleet Management System';
            $email->setFrom($fromEmail, $fromName);
            $email->setTo($toEmail);
            $email->setSubject($subject);
            $email->setMessage($body);

            if ($email->send(false)) {
                $email->clear();
                return $this->outbox($toEmail, $toUserId, $subject, $body, 'sent');
            }

            $error = $email->printDebugger(['headers']);
            return $this->outbox($toEmail, $toUserId, $subject, $body, 'failed', mb_substr(strip_tags($error), 0, 250));
        } catch (\Throwable $e) {
            return $this->outbox($toEmail, $toUserId, $subject, $body, 'failed', mb_substr($e->getMessage(), 0, 250));
        }
    }

    protected function outbox(
        ?string $toEmail,
        ?int $toUserId,
        string $subject,
        string $body,
        string $status,
        ?string $error = null
    ): string {
        try {
            $db = \Config\Database::connect();
            $db->table('email_outbox')->insert([
                'to_email'   => $toEmail,
                'to_user_id' => $toUserId,
                'subject'    => $subject,
                'body'       => $body,
                'status'     => $status,
                'error'      => $error,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'MailerService outbox failed: {msg}', ['msg' => $e->getMessage()]);
        }

        return $status;
    }
}
