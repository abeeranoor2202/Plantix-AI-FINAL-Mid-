<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Expert;
use App\Models\ExpertNotificationLog;
use App\Models\User;
use App\Services\Notifications\NotificationCenterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationCenterController extends Controller
{
    public function __construct(private readonly NotificationCenterService $service) {}

    private function currentUser(): User
    {
        foreach (['admin', 'vendor', 'expert', 'web'] as $guard) {
            $user = auth($guard)->user();
            if ($user instanceof User) {
                return $user;
            }
        }

        abort(403);
    }

    public function index(Request $request): View
    {
        $user = $this->currentUser();
        $filters = [
            'status' => (string) $request->input('status', 'all'),
            'type' => (string) $request->input('type', 'all'),
        ];

        $notifications = $this->service->listForUser($user, $filters, 20);
        $unreadCount = $this->service->unreadCount($user);

        $view = match ($user->role) {
            'admin' => 'admin.notifications.panel',
            'vendor' => 'vendor.notifications.index',
            default => 'customer.notifications',
        };

        return view($view, compact('notifications', 'unreadCount', 'filters'));
    }

    public function expertIndex(Request $request): View
    {
        $expert = $this->currentUser()->expert;
        $filters = [
            'type' => (string) $request->input('type', 'all'),
            'status' => (string) $request->input('status', 'all'),
        ];

        $notifications = $this->service->listForExpert($expert, $filters);
        $unreadCount = $this->service->unreadCountForExpert($expert);
        $latest = $this->service->latestForExpert($expert, 5);

        return view('expert.notifications.index', compact('notifications', 'unreadCount', 'filters', 'latest'));
    }

    public function feed(Request $request): JsonResponse
    {
        $user = $this->currentUser();
        $limit = max(1, min((int) $request->integer('limit', 5), 20));
        $grouped = $request->boolean('grouped', false);

        return response()->json([
            'count' => $this->service->unreadCount($user),
            'items' => $grouped
                ? $this->service->groupedPreviewForUser($user, $limit)
                : $this->service->latestForUser($user, $limit)->values(),
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        return response()->json(['count' => $this->service->unreadCount($this->currentUser())]);
    }

    public function expertUnreadCount(): JsonResponse
    {
        return response()->json(['count' => $this->service->unreadCountForExpert($this->currentUser()->expert)]);
    }

    public function markRead(Notification $notification): RedirectResponse
    {
        $this->service->markRead($notification, $this->currentUser());

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead(): RedirectResponse
    {
        $count = $this->service->markAllRead($this->currentUser());

        return back()->with('success', "{$count} notifications marked as read.");
    }

    public function clearAll(): RedirectResponse
    {
        $count = $this->service->clearAll($this->currentUser());

        return back()->with('success', "{$count} notifications cleared.");
    }

    public function open(Notification $notification): RedirectResponse
    {
        $this->service->markRead($notification, $this->currentUser());

        return redirect()->to($notification->action_url ?: route('notifications.index'));
    }

    public function expertMarkRead(ExpertNotificationLog $notification): RedirectResponse
    {
        $this->service->markExpertRead($notification, $this->currentUser()->expert);

        return back()->with('success', 'Notification marked as read.');
    }

    public function expertOpen(ExpertNotificationLog $notification): RedirectResponse
    {
        $this->service->markExpertRead($notification, $this->currentUser()->expert);

        $target = $notification->action_url ?: data_get($notification->data, 'action_url');

        if (is_string($target) && $target !== '') {
            return redirect()->to($target);
        }

        return redirect()->route('expert.notifications.index');
    }

    public function expertMarkAllRead(): RedirectResponse
    {
        $count = $this->service->markExpertAllRead($this->currentUser()->expert);

        return back()->with('success', "{$count} notifications marked as read.");
    }

    public function expertBulkRead(Request $request): RedirectResponse
    {
        $ids = (array) $request->input('ids', []);
        $count = $this->service->markExpertManyRead($this->currentUser()->expert, $ids);

        return back()->with('success', "{$count} selected notifications marked as read.");
    }

    public function expertBulkDelete(Request $request): RedirectResponse
    {
        $ids = (array) $request->input('ids', []);
        $count = $this->service->deleteExpertMany($this->currentUser()->expert, $ids);

        return back()->with('success', "{$count} selected notifications deleted.");
    }

    public function expertClearAll(): RedirectResponse
    {
        $count = $this->service->clearExpertAll($this->currentUser()->expert);

        return back()->with('success', "{$count} notifications cleared.");
    }

    public function expertFeed(Request $request): JsonResponse
    {
        $expert = $this->currentUser()->expert;
        $limit = (int) $request->integer('limit', 5);
        $items = collect($this->service->latestForExpert($expert, $limit))
            ->map(function (array $item) {
                $item['open_url'] = $item['action_url'] ?? '#';
                return $item;
            })
            ->values();

        return response()->json([
            'count' => $this->service->unreadCountForExpert($expert),
            'items' => $items,
        ]);
    }
}
