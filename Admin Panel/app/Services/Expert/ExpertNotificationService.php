<?php

namespace App\Services\Expert;

use App\Models\Appointment;
use App\Models\Expert;
use App\Models\ExpertNotificationLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

/**
 * ExpertNotificationService
 *
 * Manages expert-scoped in-app notification logs.
 * Laravel's built-in notifiable system writes to the `notifications` table;
 * this service provides additional typed log management for the expert panel.
 */
class ExpertNotificationService
{
    public const TYPE_APPOINTMENT_NEW             = 'appointment.new_request';
    public const TYPE_APPOINTMENT_STATUS          = 'appointment.status_updated';
    public const TYPE_APPOINTMENT_STARTING_SOON   = 'appointment.starting_soon';
    public const TYPE_FORUM_REPLY                 = 'forum.reply_on_answer';
    public const TYPE_FORUM_HELPFUL               = 'forum.reply_marked_helpful';
    public const TYPE_FORUM_ASSIGNED              = 'forum.thread_assigned';
    public const TYPE_PAYOUT_PROCESSED            = 'payout.processed';
    public const TYPE_PAYOUT_PENDING              = 'payout.pending';
    public const TYPE_SYSTEM_PROFILE_APPROVED     = 'system.profile_approved';
    public const TYPE_SYSTEM_ACCOUNT_WARNING      = 'system.account_warning';

    public const FILTER_TYPE_MAP = [
        'appointments' => [
            self::TYPE_APPOINTMENT_NEW,
            self::TYPE_APPOINTMENT_STATUS,
            self::TYPE_APPOINTMENT_STARTING_SOON,
        ],
        'forum' => [
            self::TYPE_FORUM_REPLY,
            self::TYPE_FORUM_HELPFUL,
            self::TYPE_FORUM_ASSIGNED,
        ],
        'payments' => [
            self::TYPE_PAYOUT_PROCESSED,
            self::TYPE_PAYOUT_PENDING,
        ],
        'system' => [
            self::TYPE_SYSTEM_PROFILE_APPROVED,
            self::TYPE_SYSTEM_ACCOUNT_WARNING,
        ],
    ];

    public const ICON_MAP = [
        self::TYPE_APPOINTMENT_NEW           => 'mdi mdi-calendar-check-outline',
        self::TYPE_APPOINTMENT_STATUS        => 'mdi mdi-calendar-clock',
        self::TYPE_APPOINTMENT_STARTING_SOON => 'mdi mdi-calendar-alert',
        self::TYPE_FORUM_REPLY               => 'mdi mdi-message-reply-text-outline',
        self::TYPE_FORUM_HELPFUL             => 'mdi mdi-message-star-outline',
        self::TYPE_FORUM_ASSIGNED            => 'mdi mdi-message-processing-outline',
        self::TYPE_PAYOUT_PROCESSED          => 'mdi mdi-wallet-outline',
        self::TYPE_PAYOUT_PENDING            => 'mdi mdi-wallet-plus-outline',
        self::TYPE_SYSTEM_PROFILE_APPROVED   => 'mdi mdi-bell-check-outline',
        self::TYPE_SYSTEM_ACCOUNT_WARNING    => 'mdi mdi-bell-alert-outline',
    ];

    public const ACTION_LABEL_MAP = [
        self::TYPE_APPOINTMENT_NEW           => 'View Appointment',
        self::TYPE_APPOINTMENT_STATUS        => 'View Appointment',
        self::TYPE_APPOINTMENT_STARTING_SOON => 'Open Session',
        self::TYPE_FORUM_REPLY               => 'Reply Now',
        self::TYPE_FORUM_HELPFUL             => 'View Thread',
        self::TYPE_FORUM_ASSIGNED            => 'View Thread',
        self::TYPE_PAYOUT_PROCESSED          => 'View Payout',
        self::TYPE_PAYOUT_PENDING            => 'View Payout',
        self::TYPE_SYSTEM_PROFILE_APPROVED   => 'Open Dashboard',
        self::TYPE_SYSTEM_ACCOUNT_WARNING    => 'Review Account',
    ];

    /**
     * Create a new notification log entry for an expert.
     */
    public function notify(
        Expert $expert,
        string $type,
        string $title,
        string $message = '',
        array $data = [],
        ?int $relatedId = null,
        ?string $actionUrl = null
    ): ExpertNotificationLog {
        return ExpertNotificationLog::create([
            'expert_id'  => $expert->id,
            'user_id'    => $expert->user_id,
            'type'       => $type,
            'title'      => $title,
            'message'    => $message,
            'body'       => $message,
            'action_url' => $actionUrl,
            'data'       => $data,
            'related_id' => $relatedId,
            'is_read'    => false,
        ]);
    }

    /**
     * Paginated list of notifications for panel display.
     */
    public function listForExpert(Expert $expert, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $this->ensureSessionStartingSoonNotifications($expert);

        $query = ExpertNotificationLog::query()
            ->where('expert_id', $expert->id)
            ->orderByDesc('created_at');

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage)->withQueryString();
    }

    public function latestForExpert(Expert $expert, int $limit = 5): array
    {
        $this->ensureSessionStartingSoonNotifications($expert);

        return ExpertNotificationLog::query()
            ->where('expert_id', $expert->id)
            ->orderByDesc('created_at')
            ->limit(max(1, min($limit, 25)))
            ->get()
            ->map(fn (ExpertNotificationLog $item) => $this->toFeedItem($item))
            ->all();
    }

    public function markRead(ExpertNotificationLog $log, Expert $expert): void
    {
        $this->guardOwnership($log, $expert);
        $log->markAsRead();
    }

    public function markManyRead(Expert $expert, array $ids): int
    {
        $ids = collect($ids)->map(fn ($id) => (int) $id)->filter()->values()->all();

        if ($ids === []) {
            return 0;
        }

        return ExpertNotificationLog::query()
            ->where('expert_id', $expert->id)
            ->whereIn('id', $ids)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    /**
     * Mark a single notification as read.
     */
    public function deleteMany(Expert $expert, array $ids): int
    {
        $ids = collect($ids)->map(fn ($id) => (int) $id)->filter()->values()->all();

        if ($ids === []) {
            return 0;
        }

        return ExpertNotificationLog::query()
            ->where('expert_id', $expert->id)
            ->whereIn('id', $ids)
            ->delete();
    }

    public function clearAll(Expert $expert): int
    {
        return ExpertNotificationLog::query()
            ->where('expert_id', $expert->id)
            ->delete();
    }

    /**
     * Mark all unread notifications for an expert as read.
     */
    public function markAllRead(Expert $expert): int
    {
        return ExpertNotificationLog::where('expert_id', $expert->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    /**
     * Count unread notifications (used in nav badge).
     */
    public function unreadCount(Expert $expert): int
    {
        $this->ensureSessionStartingSoonNotifications($expert);

        return ExpertNotificationLog::where('expert_id', $expert->id)
            ->where('is_read', false)
            ->count();
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        $type = (string) ($filters['type'] ?? 'all');
        $status = (string) ($filters['status'] ?? 'all');

        if ($type !== '' && $type !== 'all' && isset(self::FILTER_TYPE_MAP[$type])) {
            $query->whereIn('type', self::FILTER_TYPE_MAP[$type]);
        }

        if ($status === 'unread') {
            $query->where('is_read', false);
        } elseif ($status === 'read') {
            $query->where('is_read', true);
        }
    }

    private function guardOwnership(ExpertNotificationLog $log, Expert $expert): void
    {
        if ((int) $log->expert_id !== (int) $expert->id) {
            throw new \DomainException('Access denied to this notification.');
        }
    }

    private function ensureSessionStartingSoonNotifications(Expert $expert): void
    {
        $windowStart = now();
        $windowEnd = now()->addMinutes(30);

        Appointment::query()
            ->where('expert_id', $expert->id)
            ->whereIn('status', [Appointment::STATUS_CONFIRMED, Appointment::STATUS_RESCHEDULED])
            ->whereBetween('scheduled_at', [$windowStart, $windowEnd])
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get(['id', 'scheduled_at', 'user_id'])
            ->each(function (Appointment $appointment) use ($expert): void {
                $dedupKey = 'session_starting_soon_' . $appointment->id;

                $alreadyExists = ExpertNotificationLog::query()
                    ->where('expert_id', $expert->id)
                    ->where('type', self::TYPE_APPOINTMENT_STARTING_SOON)
                    ->where('data->dedup_key', $dedupKey)
                    ->exists();

                if ($alreadyExists) {
                    return;
                }

                $startsAt = Carbon::parse($appointment->scheduled_at);

                $this->notify(
                    $expert,
                    self::TYPE_APPOINTMENT_STARTING_SOON,
                    'Session starting soon',
                    'Appointment #' . $appointment->id . ' starts ' . $startsAt->diffForHumans() . '.',
                    [
                        'appointment_id' => $appointment->id,
                        'dedup_key' => $dedupKey,
                    ],
                    $appointment->user_id,
                    route('expert.appointments.show', $appointment->id)
                );
            });
    }

    private function toFeedItem(ExpertNotificationLog $item): array
    {
        $actionUrl = $item->action_url ?? data_get($item->data, 'action_url');

        return [
            'id' => (int) $item->id,
            'type' => (string) $item->type,
            'title' => (string) $item->title,
            'message' => (string) ($item->message ?? $item->body ?? ''),
            'icon' => self::ICON_MAP[$item->type] ?? 'mdi mdi-bell-outline',
            'action_label' => self::ACTION_LABEL_MAP[$item->type] ?? 'View',
            'action_url' => $actionUrl,
            'is_read' => (bool) $item->is_read,
            'created_at_human' => optional($item->created_at)?->diffForHumans() ?? '',
            'open_url' => route('expert.notifications.open', $item->id),
        ];
    }
}
