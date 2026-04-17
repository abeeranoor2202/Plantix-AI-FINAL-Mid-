<?php

namespace App\Http\Controllers;

use App\Models\Notification;
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

    public function feed(Request $request): JsonResponse
    {
        $user = $this->currentUser();
        $limit = (int) $request->integer('limit', 5);

        return response()->json([
            'count' => $this->service->unreadCount($user),
            'items' => $this->service->latestForUser($user, $limit)->values(),
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        return response()->json(['count' => $this->service->unreadCount($this->currentUser())]);
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
}
