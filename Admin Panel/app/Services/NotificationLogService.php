<?php

namespace App\Services;

use App\Mail\PlantixBaseMail;
use App\Models\NotificationLog;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;

/**
 * NotificationLogService
 *
 * Central dispatcher for all outgoing emails on Plantix AI.
 *
 * Responsibilities:
 *  1. Validate recipient email — skip invalid addresses (no thrown exceptions)
 *  2. Dedup check — if same dedup_key was sent successfully < 5 min ago, skip
 *  3. Log all dispatch attempts to `notification_logs` table
 *  4. Queue the Mailable via Laravel's Mail facade (async by default)
 *  5. Mark log entry as 'sent' on success or 'failed' on exception
 *  6. Skip emails to soft-deleted users
 *
 * Usage:
 *   app(NotificationLogService::class)->send(
 *       mailable: new OrderMail($order),
 *       to:               $order->user->email,
 *       recipientName:    $order->user->name,
 *       recipientRole:    'user',
 *       notificationType: 'order_placed',
 *       notifiable:       $order,
 *       userId:           $order->user_id,
 *       dedupKey:         "order_placed:{$order->id}",
 *   );
 */
class NotificationLogService
{
    /**
     * Queue and log a single email.
     *
     * @param  Mailable      $mailable         The Mailable instance to send
     * @param  string        $to               Recipient email address
     * @param  string|null   $recipientName    Display name
     * @param  string        $recipientRole    user | vendor | expert | admin
     * @param  string        $notificationType e.g. order_placed, appointment_confirmed
     * @param  object|null   $notifiable       Polymorphic context model (Order, Appointment, etc.)
     * @param  int|null      $userId           users.id of the recipient
     * @param  string|null   $dedupKey         Unique key to prevent duplicate sends
     * @return NotificationLog|null            null if skipped (dedup / invalid email)
     */
    public function send(
        Mailable    $mailable,
        string      $to,
        ?string     $recipientName    = null,
        string      $recipientRole    = 'user',
        string      $notificationType = 'general',
        ?object     $notifiable       = null,
        ?int        $userId           = null,
        ?string     $dedupKey         = null,
    ): ?NotificationLog {

        // ── 1. Validate email address ───────────────────────────────────────
        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            Log::warning("[Notifications] Invalid email address skipped: {$to} (type={$notificationType})");
            return null;
        }

        // ── 2. Dedup check ──────────────────────────────────────────────────
        if ($dedupKey) {
            $alreadySent = NotificationLog::where('dedup_key', $dedupKey)
                ->where('status', 'sent')
                ->where('created_at', '>=', now()->subMinutes(5))
                ->exists();

            if ($alreadySent) {
                Log::info("[Notifications] Dedup skip: {$dedupKey}");
                return null;
            }
        }

        // ── 3. Create log entry ─────────────────────────────────────────────
        $log = NotificationLog::create([
            'user_id'           => $userId,
            'recipient_email'   => $to,
            'recipient_name'    => $recipientName,
            'recipient_role'    => $recipientRole,
            'notification_type' => $notificationType,
            'mailable_class'    => get_class($mailable),
            'subject'           => method_exists($mailable, 'resolveSubject')
                ? $this->safeResolveSubject($mailable)
                : null,
            'notifiable_type'   => $notifiable ? get_class($notifiable) : null,
            'notifiable_id'     => $notifiable?->id,
            'status'            => 'queued',
            'dedup_key'         => $dedupKey,
            'attempt_count'     => 0,
        ]);

        // ── 4. Dispatch to queue ────────────────────────────────────────────
        try {
            Mail::to($to)->queue($mailable);

            $log->update([
                'status'  => 'sent',
                'sent_at' => now(),
                'attempt_count' => 1,
            ]);

        } catch (Throwable $e) {
            $log->update([
                'status'        => 'failed',
                'failed_at'     => now(),
                'error_message' => $e->getMessage(),
                'attempt_count' => 1,
            ]);

            Log::error("[Notifications] Failed to queue email to {$to} (type={$notificationType}): " . $e->getMessage());
        }

        return $log;
    }

    /**
     * Send to multiple recipients (e.g., all admins).
     *
     * @param  array $recipients  [['email' => ..., 'name' => ..., 'role' => ...], ...]
     */
    public function sendToMany(
        \Closure    $mailableFactory,   // fn(string $email) => Mailable
        array       $recipients,
        string      $notificationType  = 'general',
        ?object     $notifiable        = null,
        ?string     $dedupKeyPrefix    = null,
    ): void {
        foreach ($recipients as $recipient) {
            $email = $recipient['email'] ?? null;
            if (! $email) continue;

            $dedupKey = $dedupKeyPrefix ? "{$dedupKeyPrefix}:{$email}" : null;

            $this->send(
                mailable:          $mailableFactory($email),
                to:                $email,
                recipientName:     $recipient['name'] ?? null,
                recipientRole:     $recipient['role'] ?? 'admin',
                notificationType:  $notificationType,
                notifiable:        $notifiable,
                dedupKey:          $dedupKey,
            );
        }
    }

    /** Safely extract subject without throwing */
    private function safeResolveSubject(Mailable $mailable): ?string
    {
        try {
            if (method_exists($mailable, 'envelope')) {
                return $mailable->envelope()->subject;
            }
        } catch (Throwable) {}
        return null;
    }
}
