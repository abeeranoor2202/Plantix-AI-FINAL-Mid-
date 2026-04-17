<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ExpertNotificationLog;
use App\Models\Notification;
use App\Services\Api\V1\NotificationApiService;
use App\Services\Notifications\NotificationCenterService;
use Illuminate\Http\Request;

class NotificationController extends ApiController
{
    public function __construct(
        private readonly NotificationApiService $service,
        private readonly NotificationCenterService $center,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'type' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,unread,read'],
            'page' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $paginator = $this->service->listForActor($request->user(), $filters, (int) ($filters['limit'] ?? 20));

        return $this->paginated($paginator, $paginator->items());
    }

    public function unreadCount(Request $request)
    {
        return $this->ok(['count' => $this->service->unreadCount($request->user())]);
    }

    public function markRead(Request $request, int $id)
    {
        $actor = $request->user();

        if ($actor->role === 'expert' || $actor->role === 'agency_expert') {
            $notification = ExpertNotificationLog::query()->findOrFail($id);
            $this->center->markExpertRead($notification, $actor->expert);
        } else {
            $notification = Notification::query()->findOrFail($id);
            $this->center->markRead($notification, $actor);
        }

        return $this->ok(null, 'Notification marked as read.');
    }

    public function markAllRead(Request $request)
    {
        $actor = $request->user();

        if ($actor->role === 'expert' || $actor->role === 'agency_expert') {
            $count = $this->center->markExpertAllRead($actor->expert);
        } else {
            $count = $this->center->markAllRead($actor);
        }

        return $this->ok(['updated' => $count], 'Notifications marked as read.');
    }
}
