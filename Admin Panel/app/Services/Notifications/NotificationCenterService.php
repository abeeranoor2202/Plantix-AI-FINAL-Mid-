<?php

namespace App\Services\Notifications;

use App\Events\Appointment\AppointmentCreated;
use App\Events\Appointment\AppointmentStatusChanged;
use App\Events\Forum\ContentFlagged;
use App\Events\Forum\ForumReplyCreated;
use App\Events\Forum\OfficialAnswerMarked;
use App\Events\Order\OrderPlaced;
use App\Events\Order\OrderStatusUpdated;
use App\Events\Review\ReviewCreated;
use App\Events\Vendor\VendorStatusChanged;
use App\Events\Expert\ExpertStatusChanged;
use App\Models\Expert;
use App\Models\ExpertNotificationLog;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class NotificationCenterService
{
    public const FILTER_TYPE_MAP = [
        'appointments' => [
            'appointment.new_request',
            'appointment.status_updated',
            'appointment.starting_soon',
        ],
        'forum' => [
            'forum.reply_on_answer',
            'forum.reply_marked_helpful',
            'forum.thread_assigned',
        ],
        'payments' => [
            'payout.processed',
            'payout.pending',
        ],
        'system' => [
            'system.profile_approved',
            'system.account_warning',
        ],
    ];

    public function notify(
        ?User $sender,
        User $receiver,
        string $type,
        string $message,
        ?string $actionUrl = null,
        array $metadata = [],
        ?string $title = null,
        ?string $dedupKey = null,
    ): ?Notification {
        if (! $this->notificationsTableExists()) {
            return null;
        }

        $title = $title ?: Str::of($type)->replace(['_', '.'], ' ')->title()->toString();

        $resolvedDedupKey = $dedupKey ?: md5(implode('|', [
            $sender?->id ?? 0,
            $receiver->id,
            $receiver->role ?? 'user',
            $type,
            $message,
            (string) $actionUrl,
        ]));

        $existing = Notification::query()
            ->where('receiver_id', $receiver->id)
            ->where('role', $receiver->role === 'expert' ? 'expert' : ($receiver->role === 'vendor' ? 'vendor' : ($receiver->role === 'admin' ? 'admin' : 'user')))
            ->where('dedup_key', $resolvedDedupKey)
            ->where('created_at', '>=', now()->subMinutes(3))
            ->first();

        if ($existing) {
            return $existing;
        }

        return Notification::create([
            'sender_id' => $sender?->id,
            'receiver_id' => $receiver->id,
            'role' => $receiver->role === 'expert' ? 'expert' : ($receiver->role === 'vendor' ? 'vendor' : ($receiver->role === 'admin' ? 'admin' : 'user')),
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'status' => 'unread',
            'action_url' => $actionUrl,
            'metadata' => array_merge($metadata, ['dedup_key' => $resolvedDedupKey]),
            'dedup_key' => $resolvedDedupKey,
            'read' => false,
            'sent_at' => now(),
        ]);
    }

    public function notifyMany(iterable $receivers, ?User $sender, string $type, string $message, ?string $actionUrl = null, array $metadata = [], ?string $title = null): int
    {
        $count = 0;

        foreach ($receivers as $receiver) {
            if (! $receiver instanceof User) {
                continue;
            }

            if ($this->notify($sender, $receiver, $type, $message, $actionUrl, $metadata, $title)) {
                $count++;
            }
        }

        return $count;
    }

    public function notifyExpert(
        Expert $expert,
        string $type,
        string $title,
        string $message = '',
        array $data = [],
        ?int $relatedId = null,
        ?string $actionUrl = null
    ): ExpertNotificationLog {
        return ExpertNotificationLog::create([
            'expert_id' => $expert->id,
            'user_id' => $expert->user_id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'body' => $message,
            'action_url' => $actionUrl,
            'data' => $data,
            'related_id' => $relatedId,
            'is_read' => false,
        ]);
    }

    public function unreadCount(User $user): int
    {
        if (! $this->notificationsTableExists()) {
            return 0;
        }

        return Notification::query()
            ->where('receiver_id', $user->id)
            ->where('role', $this->roleFor($user))
            ->where('status', 'unread')
            ->count();
    }

    public function latestForUser(User $user, int $limit = 5): Collection
    {
        if (! $this->notificationsTableExists()) {
            return collect();
        }

        return Notification::query()
            ->where('receiver_id', $user->id)
            ->where('role', $this->roleFor($user))
            ->latest('created_at')
            ->limit(max(1, min($limit, 25)))
            ->get()
            ->map(fn (Notification $notification) => $this->toFeedItem($notification));
    }

    public function groupedPreviewForUser(User $user, int $limit = 5): array
    {
        if (! $this->notificationsTableExists()) {
            return [];
        }

        $items = $this->latestForUser($user, max($limit, 10));
        $grouped = $items
            ->groupBy(fn (array $item) => (string) ($item['type'] ?? 'system'))
            ->map(function (Collection $group, string $type) {
                $first = $group->first();

                return [
                    'type' => $type,
                    'count' => $group->count(),
                    'title' => $first['title'] ?? 'Notification',
                    'message' => $group->count() > 1
                        ? $group->count() . ' updates for ' . str_replace(['.', '_'], ' ', $type)
                        : ($first['message'] ?? ''),
                    'created_at_human' => $first['created_at_human'] ?? '',
                    'open_url' => $first['open_url'] ?? '#',
                    'is_read' => $group->every(fn (array $item) => (bool) ($item['is_read'] ?? false)),
                ];
            })
            ->take($limit)
            ->values()
            ->all();

        return $grouped;
    }

    public function listForUser(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        if (! $this->notificationsTableExists()) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        $query = Notification::query()
            ->where('receiver_id', $user->id)
            ->where('role', $this->roleFor($user))
            ->latest('created_at');

        $status = (string) ($filters['status'] ?? 'all');
        $type = (string) ($filters['type'] ?? 'all');

        if ($status === 'unread') {
            $query->where('status', 'unread');
        } elseif ($status === 'read') {
            $query->where('status', 'read');
        }

        if ($type !== 'all' && $type !== '') {
            $query->where('type', $type);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function markRead(Notification $notification, User $user): void
    {
        if (! $this->notificationsTableExists()) {
            return;
        }

        $this->guard($notification, $user);

        $notification->update([
            'status' => 'read',
            'read' => true,
            'read_at' => now(),
        ]);
    }

    public function markAllRead(User $user): int
    {
        if (! $this->notificationsTableExists()) {
            return 0;
        }

        return Notification::query()
            ->where('receiver_id', $user->id)
            ->where('role', $this->roleFor($user))
            ->where('status', 'unread')
            ->update([
                'status' => 'read',
                'read' => true,
                'read_at' => now(),
            ]);
    }

    public function clearAll(User $user): int
    {
        if (! $this->notificationsTableExists()) {
            return 0;
        }

        return Notification::query()
            ->where('receiver_id', $user->id)
            ->where('role', $this->roleFor($user))
            ->delete();
    }

    public function listForExpert(Expert $expert, array $filters = [], int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = ExpertNotificationLog::query()
            ->where('expert_id', $expert->id)
            ->orderByDesc('created_at');

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

        return $query->paginate($perPage)->withQueryString();
    }

    public function latestForExpert(Expert $expert, int $limit = 5): array
    {
        return ExpertNotificationLog::query()
            ->where('expert_id', $expert->id)
            ->orderByDesc('created_at')
            ->limit(max(1, min($limit, 25)))
            ->get()
            ->map(fn (ExpertNotificationLog $item) => [
                'id' => (int) $item->id,
                'title' => (string) ($item->title ?? 'Notification'),
                'message' => (string) $item->display_message,
                'type' => (string) $item->type,
                'is_read' => (bool) $item->is_read,
                'created_at_human' => optional($item->created_at)?->diffForHumans() ?? '',
                'action_url' => $item->action_url ?: data_get($item->data, 'action_url'),
            ])
            ->all();
    }

    public function unreadCountForExpert(Expert $expert): int
    {
        return ExpertNotificationLog::where('expert_id', $expert->id)->where('is_read', false)->count();
    }

    public function markExpertRead(ExpertNotificationLog $log, Expert $expert): void
    {
        $this->guardExpertOwnership($log, $expert);
        $log->markAsRead();
    }

    public function markExpertAllRead(Expert $expert): int
    {
        return ExpertNotificationLog::where('expert_id', $expert->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    public function markExpertManyRead(Expert $expert, array $ids): int
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

    public function deleteExpertMany(Expert $expert, array $ids): int
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

    public function clearExpertAll(Expert $expert): int
    {
        return ExpertNotificationLog::query()->where('expert_id', $expert->id)->delete();
    }

    private function guardExpertOwnership(ExpertNotificationLog $log, Expert $expert): void
    {
        if ((int) $log->expert_id !== (int) $expert->id) {
            abort(403);
        }
    }

    public function guard(Notification $notification, User $user): void
    {
        if ((int) $notification->receiver_id !== (int) $user->id || $notification->role !== $this->roleFor($user)) {
            abort(403);
        }
    }

    public function toFeedItem(Notification $notification): array
    {
        return [
            'id' => (int) $notification->id,
            'title' => (string) ($notification->title ?? 'Notification'),
            'message' => (string) ($notification->message ?? ''),
            'type' => (string) $notification->type,
            'is_read' => (bool) ($notification->status === 'read' || $notification->read),
            'created_at_human' => optional($notification->created_at)?->diffForHumans() ?? '',
            'action_url' => $notification->action_url,
            'open_url' => $notification->action_url ?: '#',
        ];
    }

    private function roleFor(User $user): string
    {
        return match ($user->role) {
            'vendor' => 'vendor',
            'expert', 'agency_expert' => 'expert',
            'admin' => 'admin',
            default => 'user',
        };
    }

    private function notificationsTableExists(): bool
    {
        return Schema::hasTable((new Notification())->getTable());
    }

    public function syncFromEvent(object $event): void
    {
        match (true) {
            $event instanceof ForumReplyCreated => $this->syncForumReplyCreated($event),
            $event instanceof OfficialAnswerMarked => $this->syncOfficialAnswerMarked($event),
            $event instanceof ContentFlagged => $this->syncContentFlagged($event),
            $event instanceof OrderPlaced => $this->syncOrderPlaced($event),
            $event instanceof OrderStatusUpdated => $this->syncOrderStatusUpdated($event),
            $event instanceof ReviewCreated => $this->syncReviewCreated($event),
            $event instanceof AppointmentCreated => $this->syncAppointmentCreated($event),
            $event instanceof AppointmentStatusChanged => $this->syncAppointmentStatusChanged($event),
            $event instanceof VendorStatusChanged => $this->syncVendorStatusChanged($event),
            $event instanceof ExpertStatusChanged => $this->syncExpertStatusChanged($event),
            default => null,
        };
    }

    private function syncForumReplyCreated(ForumReplyCreated $event): void
    {
        if ($event->thread->user && $event->reply->user_id !== $event->thread->user_id) {
            $this->notify(
                $event->reply->user,
                $event->thread->user,
                'forum',
                'Someone replied to your forum thread.',
                route('forum.thread', $event->thread->slug),
                ['thread_id' => $event->thread->id, 'reply_id' => $event->reply->id],
                'Forum reply'
            );
        }
    }

    private function syncOfficialAnswerMarked(OfficialAnswerMarked $event): void
    {
        if ($event->reply->user && $event->reply->user_id !== $event->thread->user_id) {
            $this->notify($event->reply->user, $event->thread->user, 'forum', 'Your thread received an official answer.', route('forum.thread', $event->thread->slug), ['thread_id' => $event->thread->id], 'Official answer');
        }
    }

    private function syncContentFlagged(ContentFlagged $event): void
    {
        $admins = User::where('role', 'admin')->where('active', true)->get();
        $sender = $event->flag->reporter ?? null;
        foreach ($admins as $admin) {
            $this->notify($sender, $admin, 'forum', 'A new report was submitted.', route('admin.forum.flags.index'), ['flag_id' => $event->flag->id], 'New report');
        }
    }

    private function syncOrderPlaced(OrderPlaced $event): void
    {
        $vendorUser = $event->order->vendor?->author;
        if ($vendorUser) {
            $this->notify($event->order->user, $vendorUser, 'order', 'A new order has been placed in your store.', route('vendor.orders.show', $event->order->id), ['order_id' => $event->order->id], 'New order');
        }
    }

    private function syncOrderStatusUpdated(OrderStatusUpdated $event): void
    {
        if ($event->order->user) {
            $this->notify(null, $event->order->user, 'order', "Your order status changed to {$event->newStatus}.", route('order.details', $event->order->id), ['order_id' => $event->order->id, 'status' => $event->newStatus], 'Order update');
        }
    }

    private function syncReviewCreated(ReviewCreated $event): void
    {
        if ($event->vendor->author) {
            $this->notify($event->review->user ?? null, $event->vendor->author, 'system', 'You received a new review.', route('vendor.reviews.index'), ['review_id' => $event->review->id], 'New review');
        }
    }

    private function syncAppointmentCreated(AppointmentCreated $event): void
    {
        if ($event->appointment->expert?->user) {
            $this->notify($event->appointment->user, $event->appointment->expert->user, 'appointment', 'You have a new appointment request.', route('expert.appointments.show', $event->appointment->id), ['appointment_id' => $event->appointment->id], 'New appointment');
        }

        $admins = User::where('role', 'admin')->where('active', true)->get();
        foreach ($admins as $admin) {
            $this->notify($event->appointment->user, $admin, 'appointment', 'A new appointment was created.', route('admin.appointments.show', $event->appointment->id), ['appointment_id' => $event->appointment->id], 'Appointment created');
        }
    }

    private function syncAppointmentStatusChanged(AppointmentStatusChanged $event): void
    {
        if ($event->appointment->user) {
            $this->notify(null, $event->appointment->user, 'appointment', "Your appointment was {$event->newStatus}.", route('appointment.details', $event->appointment->id), ['appointment_id' => $event->appointment->id, 'status' => $event->newStatus], 'Appointment update');
        }
    }

    private function syncVendorStatusChanged(VendorStatusChanged $event): void
    {
        if ($event->vendor->author) {
            $this->notify(null, $event->vendor->author, 'system', "Your vendor profile was {$event->status}.", route('vendor.dashboard'), ['vendor_id' => $event->vendor->id, 'status' => $event->status], 'Vendor status');
        }
    }

    private function syncExpertStatusChanged(ExpertStatusChanged $event): void
    {
        if ($event->expert->user) {
            $this->notify(null, $event->expert->user, 'system', "Your expert profile was {$event->status}.", route('expert.dashboard'), ['expert_id' => $event->expert->id, 'status' => $event->status], 'Expert status');
        }
    }
}
