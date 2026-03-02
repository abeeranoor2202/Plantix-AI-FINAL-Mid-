<?php

namespace App\Jobs;

use App\Mail\CustomNotificationMail;
use App\Models\User;
use App\Notifications\CustomNotification;
use App\Services\NotificationLogService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * SendCustomNotificationJob
 *
 * Handles sending custom notifications to users via multiple channels.
 * - In-app: Stored in `notifications` table (database channel)
 * - Email: Sent via SMTP and logged in `notification_logs` table
 *
 * Features:
 * - Automatic retry with exponential backoff
 * - Comprehensive error logging
 * - Email delivery tracking via NotificationLogService
 * - Deduplication to prevent duplicate sends
 *
 * Queue: notifications
 * Tries: 3 with exponential backoff
 */
class SendCustomNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        private readonly array $userIds,
        private readonly string $title,
        private readonly string $body,
        private readonly ?string $actionUrl = null,
        private readonly bool $sendEmail = false,
    ) {
        $this->queue = 'notifications';
    }

    public function handle(NotificationLogService $notifLog): void
    {
        $users = User::whereIn('id', $this->userIds)
            ->where('active', true)
            ->get();

        $successCount = 0;
        $failureCount = 0;

        foreach ($users as $user) {
            try {
                // Send in-app notification (always)
                $user->notify(
                    new CustomNotification(
                        $this->title,
                        $this->body,
                        $this->actionUrl,
                        $this->sendEmail
                    )
                );

                // Track email delivery if requested
                if ($this->sendEmail && $user->email) {
                    $notifLog->send(
                        mailable: new CustomNotificationMail(
                            $user,
                            $this->title,
                            $this->body,
                            $this->actionUrl
                        ),
                        to: $user->email,
                        recipientName: $user->name,
                        recipientRole: $user->role,
                        notificationType: 'custom_admin_notification',
                        userId: $user->id,
                        dedupKey: "admin_custom_{$user->id}_" . md5($this->title . $this->body . now()->format('Y-m-d')),
                    );
                }

                $successCount++;

                Log::info("Custom notification processed for user {$user->id}", [
                    'user_id'    => $user->id,
                    'user_email' => $user->email,
                    'title'      => $this->title,
                    'with_email' => $this->sendEmail,
                    'channel'    => $this->sendEmail ? 'in-app + email' : 'in-app',
                ]);
            } catch (\Throwable $e) {
                $failureCount++;

                Log::error("Custom notification failed for user {$user->id}", [
                    'user_id'    => $user->id,
                    'user_email' => $user->email ?? 'N/A',
                    'user_name'  => $user->name ?? 'N/A',
                    'user_role'  => $user->role ?? 'N/A',
                    'title'      => $this->title,
                    'with_email' => $this->sendEmail,
                    'error'      => $e->getMessage(),
                    'file'       => $e->getFile(),
                    'line'       => $e->getLine(),
                ]);

                // Log the failure for recovery/retry purposes
                if ($this->sendEmail && $user->email) {
                    try {
                        \DB::table('notification_logs')->insertOrIgnore([
                            'user_id'            => $user->id,
                            'recipient_email'    => $user->email,
                            'recipient_name'     => $user->name,
                            'recipient_role'     => $user->role,
                            'notification_type'  => 'custom_admin_notification',
                            'mailable_class'     => CustomNotificationMail::class,
                            'subject'            => $this->title,
                            'status'             => 'failed',
                            'error_message'      => substr($e->getMessage(), 0, 1000),
                            'attempt_count'      => 1,
                            'failed_at'          => now(),
                            'created_at'         => now(),
                            'updated_at'         => now(),
                        ]);
                    } catch (\Throwable $logError) {
                        Log::error("Failed to log notification error: " . $logError->getMessage());
                    }
                }
            }
        }

        Log::info("SendCustomNotificationJob completed", [
            'total_users'       => count($this->userIds),
            'success_count'     => $successCount,
            'failure_count'     => $failureCount,
            'with_email'        => $this->sendEmail,
        ]);
    }

    public function backoff(): array
    {
        return [30, 60, 120]; // seconds before each retry
    }

    public function failed(\Throwable $exception): void
    {
        Log::critical('SendCustomNotificationJob FAILED after all retries', [
            'user_ids'       => $this->userIds,
            'total_users'    => count($this->userIds),
            'title'          => $this->title,
            'with_email'     => $this->sendEmail,
            'error'          => $exception->getMessage(),
            'exception_type' => get_class($exception),
            'file'           => $exception->getFile(),
            'line'           => $exception->getLine(),
        ]);

        // Store failed job information for admin review
        try {
            \DB::table('notification_logs')->insert([
                'recipient_email'    => 'admin@system',
                'recipient_name'     => 'System Error',
                'recipient_role'     => 'system',
                'notification_type'  => 'job_failure_report',
                'mailable_class'     => 'SendCustomNotificationJob',
                'subject'            => "Notification Job Failed: {$this->title}",
                'status'             => 'failed',
                'error_message'      => "Job failed after retries affecting " . count($this->userIds) . " users: " . substr($exception->getMessage(), 0, 500),
                'attempt_count'      => 0,
                'failed_at'          => now(),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error("Could not log job failure: " . $e->getMessage());
        }
    }
}
