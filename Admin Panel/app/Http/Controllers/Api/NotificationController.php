<?php

namespace App\Http\Controllers\Api;

use App\Models\Notification;
use App\Services\Platform\PlatformActivityService;
use App\Support\Api\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    use ApiResponder;

    public function __construct(
        private readonly PlatformActivityService $activity,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userId = auth()->id();

        $notifications = Notification::query()
            ->where(function ($q) use ($userId): void {
                $q->where('receiver_id', $userId)
                    ->orWhere('recipient_id', $userId);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return $this->paginated($notifications, $notifications->items(), 'Notifications fetched.');
    }

    public function store(Request $request): JsonResponse
    {
        if (! in_array((string) auth()->user()?->role, ['admin', 'staff'], true)) {
            return $this->fail('Forbidden.', null, 403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'type' => ['required', 'string', 'max:100'],
            'recipient_id' => ['required', 'integer', 'exists:users,id'],
            'metadata' => ['nullable', 'array'],
            'action_url' => ['nullable', 'string', 'max:500'],
        ]);

        $notification = Notification::create([
            'sender_id' => auth()->id(),
            'receiver_id' => (int) $validated['recipient_id'],
            'recipient_id' => (int) $validated['recipient_id'],
            'title' => $validated['title'],
            'message' => $validated['message'],
            'status' => 'unread',
            'read' => false,
            'action_url' => $validated['action_url'] ?? null,
            'metadata' => array_merge($validated['metadata'] ?? [], ['type' => $validated['type']]),
            'sent_at' => now(),
        ]);

        $this->activity->log(
            actorUserId: auth()->id(),
            action: 'notification.created',
            entityType: 'notification',
            entityId: $notification->id,
            context: [
                'receiver_id' => (int) $validated['recipient_id'],
                'type' => $validated['type'],
            ]
        );

        return $this->created($notification, 'Notification created.');
    }

    public function markAsRead(Notification $notification): JsonResponse
    {
        $this->authorizeNotificationAccess($notification);

        if (! $notification->read) {
            $notification->update([
                'read' => true,
                'status' => 'read',
                'read_at' => now(),
            ]);

            $this->activity->log(
                actorUserId: auth()->id(),
                action: 'notification.read',
                entityType: 'notification',
                entityId: $notification->id,
                context: []
            );
        }

        return $this->ok($notification->fresh(), 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $userId = auth()->id();

        $updated = Notification::query()
            ->where(function ($q) use ($userId): void {
                $q->where('receiver_id', $userId)
                    ->orWhere('recipient_id', $userId);
            })
            ->where('read', false)
            ->update([
                'read' => true,
                'status' => 'read',
                'read_at' => now(),
            ]);

        $this->activity->log(
            actorUserId: auth()->id(),
            action: 'notification.read_all',
            entityType: 'notification',
            entityId: null,
            context: ['updated' => $updated]
        );

        return $this->ok(['updated' => $updated], 'All notifications marked as read.');
    }

    public function destroy(Notification $notification): JsonResponse
    {
        $this->authorizeNotificationAccess($notification);

        $id = $notification->id;
        $notification->delete();

        $this->activity->log(
            actorUserId: auth()->id(),
            action: 'notification.deleted',
            entityType: 'notification',
            entityId: $id,
            context: []
        );

        return $this->ok(null, 'Notification deleted.');
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $count = Notification::query()
            ->where(function ($q) use ($userId): void {
                $q->where('receiver_id', $userId)
                    ->orWhere('recipient_id', $userId);
            })
            ->where('read', false)
            ->count();

        return $this->ok(['unread_count' => $count], 'Unread count fetched.');
    }

    private function authorizeNotificationAccess(Notification $notification): void
    {
        $userId = (int) auth()->id();
        $receiverId = (int) ($notification->receiver_id ?? 0);
        $recipientId = (int) ($notification->recipient_id ?? 0);

        if (! in_array($userId, [$receiverId, $recipientId], true)) {
            abort(403, 'Forbidden');
        }

    }
}
