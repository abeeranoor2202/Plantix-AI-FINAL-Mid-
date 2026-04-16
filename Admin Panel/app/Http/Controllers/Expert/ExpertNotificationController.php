<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Models\ExpertNotificationLog;
use App\Services\Expert\ExpertNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * ExpertNotificationController
 *
 * Handles listing, reading, and clearing expert-scoped notifications.
 */
class ExpertNotificationController extends Controller
{
    public function __construct(
        private readonly ExpertNotificationService $service
    ) {}

    private function currentExpert(): \App\Models\Expert
    {
        return auth('expert')->user()->expert;
    }

    public function index(Request $request): View
    {
        $expert        = $this->currentExpert();
        $filters = [
            'type' => (string) $request->input('type', 'all'),
            'status' => (string) $request->input('status', 'all'),
        ];

        $notifications = $this->service->listForExpert($expert, $filters);
        $unreadCount   = $this->service->unreadCount($expert);
        $latest        = $this->service->latestForExpert($expert, 5);

        return view('expert.notifications.index', compact('notifications', 'unreadCount', 'filters', 'latest'));
    }

    public function markRead(ExpertNotificationLog $notification): RedirectResponse
    {
        $this->service->markRead($notification, $this->currentExpert());

        return back()->with('success', 'Notification marked as read.');
    }

    public function open(ExpertNotificationLog $notification): RedirectResponse
    {
        $this->service->markRead($notification, $this->currentExpert());

        $target = $notification->action_url ?: data_get($notification->data, 'action_url');

        if (is_string($target) && $target !== '') {
            return redirect()->to($target);
        }

        return redirect()->route('expert.notifications.index');
    }

    public function markAllRead(): RedirectResponse
    {
        $count = $this->service->markAllRead($this->currentExpert());

        return back()->with('success', "{$count} notifications marked as read.");
    }

    public function bulkRead(Request $request): RedirectResponse
    {
        $ids = (array) $request->input('ids', []);
        $count = $this->service->markManyRead($this->currentExpert(), $ids);

        return back()->with('success', "{$count} selected notifications marked as read.");
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $ids = (array) $request->input('ids', []);
        $count = $this->service->deleteMany($this->currentExpert(), $ids);

        return back()->with('success', "{$count} selected notifications deleted.");
    }

    public function clearAll(): RedirectResponse
    {
        $count = $this->service->clearAll($this->currentExpert());

        return back()->with('success', "{$count} notifications cleared.");
    }

    /**
     * JSON endpoint for the nav-bar badge (unread count).
     */
    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'count' => $this->service->unreadCount($this->currentExpert()),
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $expert = $this->currentExpert();
        $limit = (int) $request->integer('limit', 5);

        return response()->json([
            'count' => $this->service->unreadCount($expert),
            'items' => $this->service->latestForExpert($expert, $limit),
        ]);
    }
}
