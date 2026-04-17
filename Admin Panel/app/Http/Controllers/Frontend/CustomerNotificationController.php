<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\Notifications\NotificationCenterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CustomerNotificationController
 *
 * Lists and manages real-time notifications for the authenticated customer.
 * Notifications are stored in the `real_time_notifications` table via \App\Models\Notification.
 */
class CustomerNotificationController extends Controller
{
    public function __construct(private readonly NotificationCenterService $service) {}

    private function currentUser(): User
    {
        $user = auth('web')->user();

        if ($user instanceof User) {
            return $user;
        }

        abort(403);
    }

    /**
     * List all notifications for the authenticated user.
     * Route: GET /notifications
     */
    public function index(Request $request): View
    {
        $user = $this->currentUser();
        $filters = [
            'status' => (string) $request->input('status', 'all'),
            'type' => (string) $request->input('type', 'all'),
        ];

        $notifications = $this->service->listForUser($user, $filters, 20);
        $unreadCount = $this->service->unreadCount($user);

        return view('customer.notifications', compact('notifications', 'unreadCount', 'filters'));
    }

    /**
     * Mark a single notification as read.
     * Route: POST /notifications/{id}/read
     */
    public function markRead(int $id): RedirectResponse
    {
        $notification = Notification::query()->findOrFail($id);

        $this->service->markRead($notification, $this->currentUser());

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     * Route: POST /notifications/read-all
     */
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
}
