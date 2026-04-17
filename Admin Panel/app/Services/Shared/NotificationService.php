<?php

namespace App\Services\Shared;

use App\Mail\CustomNotificationMail;
use App\Models\User;
use App\Services\NotificationLogService;
use App\Services\Notifications\NotificationCenterService;
use Illuminate\Support\Facades\Log;

/**
 * NotificationService
 *
 * Delivers notifications to users via multiple channels:
 * 1. In-app (database-channel) – stored in notifications table
 * 2. Email (SMTP) – sent via Mail driver
 *
 * Supports both sync and async (queued) notifications.
 */
class NotificationService
{
    private const CHUNK_SIZE = 100;

    // -------------------------------------------------------------------------
    // Public API – Single User Notifications
    // -------------------------------------------------------------------------

    /**
     * Send a notification to a single user (in-app + optional email).
     *
     * @param User $user
     * @param string $title
     * @param string $body
     * @param array $data
     * @param bool $sendEmail
     * @return bool Success status
     */
    public function sendToUser(
        User $user,
        string $title,
        string $body = '',
        array $data = [],
        bool $sendEmail = false
    ): bool {
        try {
            $notificationType = (string) ($data['type'] ?? 'custom_notification');
            $message = $body !== '' ? $body : $title;

            app(NotificationCenterService::class)->notify(
                sender: null,
                receiver: $user,
                type: $notificationType,
                message: $message,
                actionUrl: $data['action_url'] ?? null,
                metadata: array_merge($data, ['title' => $title]),
                title: $title,
                dedupKey: $data['dedup_key'] ?? null,
            );

            if (! $sendEmail || ! $user->email) {
                return true;
            }

            return (bool) app(NotificationLogService::class)->send(
                mailable: new CustomNotificationMail($user, $title, $message, $data['action_url'] ?? null),
                to: $user->email,
                recipientName: $user->name,
                recipientRole: $user->role,
                notificationType: $notificationType,
                notifiable: $user,
                userId: $user->id,
                dedupKey: $data['dedup_key'] ?? null,
            );
        } catch (\Throwable $e) {
            Log::error('NotificationService::sendToUser failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'title'   => $title,
            ]);
            return false;
        }
    }

    /**
     * Send a notification to multiple users (in-app + optional email).
     * Uses chunking to avoid memory overload. Sends immediately (synchronously).
     *
     * @param array $users
     * @param string $title
     * @param string $body
     * @param array $data
     * @param bool $sendEmail
     * @return int Total users notified
     */
    public function sendToMany(
        array $users,
        string $title,
        string $body = '',
        array $data = [],
        bool $sendEmail = false
    ): int {
        $totalDispatched = 0;
        $userIds         = collect($users)
            ->map(fn($user) => $user instanceof User ? $user->id : $user)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        collect($userIds)
            ->chunk(self::CHUNK_SIZE)
            ->each(function ($chunk) use ($title, $body, $data, $sendEmail, &$totalDispatched) {
                $recipients = User::whereIn('id', $chunk->toArray())
                    ->where('active', true)
                    ->get(['id', 'name', 'email', 'role']);

                if ($recipients->isEmpty()) {
                    return;
                }

                $message = $body !== '' ? $body : $title;
                $notificationType = (string) ($data['type'] ?? 'custom_notification');

                app(NotificationCenterService::class)->notifyMany(
                    $recipients,
                    null,
                    $notificationType,
                    $message,
                    $data['action_url'] ?? null,
                    array_merge($data, ['title' => $title]),
                    $title,
                );

                foreach ($recipients as $recipient) {
                    if (! $sendEmail || ! $recipient->email) {
                        $totalDispatched++;
                        continue;
                    }

                    $result = app(NotificationLogService::class)->send(
                        mailable: new CustomNotificationMail(
                            $recipient,
                            $title,
                            $message,
                            $data['action_url'] ?? null,
                        ),
                        to: $recipient->email,
                        recipientName: $recipient->name,
                        recipientRole: $recipient->role,
                        notificationType: $notificationType,
                        userId: $recipient->id,
                        dedupKey: $data['dedup_key'] ?? null,
                    );

                    if ($result) {
                        $totalDispatched++;
                    }
                }
            });

        return $totalDispatched;
    }

    // -------------------------------------------------------------------------
    // Public API – Role-Based Broadcast Notifications  
    // -------------------------------------------------------------------------

    /**
     * Send notification immediately to all users of a specific role (synchronous).
     * Sends are processed immediately in the background, like password reset emails.
     *
     * @param string $role
     * @param string $title
     * @param string $body
     * @param bool $sendEmail
     * @return int Total users notified
     */
    public function sendToRole(
        string $role,
        string $title,
        string $body = '',
        bool $sendEmail = false
    ): int {
        $users = User::where('role', $role)
            ->where('active', true)
            ->select('id')
            ->get()
            ->pluck('id')
            ->all();

        return $this->sendToMany($users, $title, $body, [], $sendEmail);
    }

    /**
     * Send notification immediately to all active users (synchronous).
     * Sends are processed immediately in the background, like password reset emails.
     *
     * @param string $title
     * @param string $body
     * @param bool $sendEmail
     * @return int Total users notified
     */
    public function sendToAll(
        string $title,
        string $body = '',
        bool $sendEmail = false
    ): int {
        $users = User::where('active', true)
            ->select('id')
            ->get()
            ->pluck('id')
            ->all();

        return $this->sendToMany($users, $title, $body, [], $sendEmail);
    }

    // -------------------------------------------------------------------------
    // Public API – Order Status Notifications (Legacy Support)
    // -------------------------------------------------------------------------

    /**
     * Helper specifically for order-status change notifications.
     * Sends via database only (not email) – can be enhanced if needed.
     */
    public function sendOrderStatusNotification(
        User   $user,
        string $orderNumber,
        string $status,
        string $orderId
    ): bool {
        $messages = [
            'confirmed'        => "Your order #{$orderNumber} has been confirmed!",
            'processing'       => "Your order #{$orderNumber} is being prepared.",
            'shipped'          => "Your order #{$orderNumber} is on the way.",
            'delivered'        => "Your order #{$orderNumber} has been delivered. Enjoy!",
            'cancelled'        => "Your order #{$orderNumber} has been cancelled.",
            'rejected'         => "Your order #{$orderNumber} was rejected.",
            'return_requested' => "Return request for order #{$orderNumber} received.",
            'returned'         => "Your return for order #{$orderNumber} has been processed.",
        ];

        $body = $messages[$status] ?? "Order #{$orderNumber} status updated to {$status}.";

        return $this->sendToUser($user, 'Order Update', $body, [
            'order_id' => (string) $orderId,
            'status'   => $status,
            'type'     => 'order_status',
            'dedup_key' => "order_status:{$orderId}:{$status}",
        ], true);
    }

    // -------------------------------------------------------------------------
    // Admin Custom Notifications API
    // -------------------------------------------------------------------------

    /**
     * Send a custom admin notification to a single user.
     * This is typically used by admins via the UI to send personalized messages.
     *
     * @param User $user
     * @param string $title
     * @param string $body
     * @param string|null $actionUrl Optional URL for CTA button
     * @param bool $sendEmail Whether to also send via email/SMTP
     * @return bool
     *
     * Example:
     *   $notificationService->sendAdminNotification(
     *       user: $user,
     *       title: "Promotion Alert",
     *       body: "A new seasonal promotion is available for you!",
     *       actionUrl: route('shop'),
     *       sendEmail: true
     *   );
     */
    public function sendAdminNotification(
        User $user,
        string $title,
        string $body,
        ?string $actionUrl = null,
        bool $sendEmail = false
    ): bool {
        return $this->sendToUser($user, $title, $body, [
            'type' => 'admin_notification',
            'action_url' => $actionUrl,
            'dedup_key' => 'admin_notification:' . md5($title . $body . (string) $actionUrl),
        ], $sendEmail);
    }

    /**
     * Send a custom admin notification immediately to all users of a specific role (synchronous).
     * Uses batch processing for efficiency. Sends are processed immediately like password reset emails.
     *
     * @param string $role customer | vendor | expert | admin
     * @param string $title
     * @param string $body
     * @param string|null $actionUrl Optional URL for CTA button
     * @param bool $sendEmail Whether to also send via email/SMTP
     * @return int Total users notified
     *
     * Example:
     *   $count = $notificationService->broadcastAdminNotification(
     *       role: 'customer',
     *       title: "System Maintenance",
     *       body: "We will be performing scheduled maintenance tonight.",
     *       sendEmail: true
     *   );
     *   echo "Notified {$count} customers";
     */
    public function broadcastAdminNotification(
        string $role,
        string $title,
        string $body,
        ?string $actionUrl = null,
        bool $sendEmail = false
    ): int {
        $users = User::where('role', $role)
            ->where('active', true)
            ->select('id')
            ->get()
            ->pluck('id')
            ->all();

        return $this->sendToMany($users, $title, $body, [
            'type' => 'admin_notification',
            'action_url' => $actionUrl,
        ], $sendEmail);
    }

    /**
     * Send to all administrators.
     * Useful for system alerts and critical updates.
     *
     * @param string $title
     * @param string $body
     * @param bool $sendEmail
     * @return int
     */
    public function notifyAdmins(
        string $title,
        string $body,
        bool $sendEmail = false
    ): int {
        return $this->broadcastAdminNotification('admin', $title, $body, null, $sendEmail);
    }

    /**
     * Legacy method – stores only in-app notifications (no email).
     * @deprecated Use sendToUser() or sendToMany() with $sendEmail parameter
     */
    public function sendToUserLegacy(User $user, string $title, string $body = '', array $data = []): void
    {
        try {
            $this->store($user, $title, $body, $data);
        } catch (\Throwable $e) {
            Log::error('NotificationService::sendToUserLegacy failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Internal Helpers
    // -------------------------------------------------------------------------

    /**
     * Store a notification in the database table.
     * Used when only in-app notification is needed.
     */
    private function store(User $user, string $title, string $body, array $data): void
    {
        $this->sendToUser($user, $title, $body, $data, true);
    }
}



