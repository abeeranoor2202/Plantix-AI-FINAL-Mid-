<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Expert;
use App\Models\PlatformActivity;
use App\Services\Expert\ExpertAppointmentService;
use App\Services\Expert\ExpertForumService;
use App\Services\Notifications\NotificationCenterService;
use Illuminate\View\View;

/**
 * ExpertDashboardController
 *
 * Renders the main expert dashboard with summary stats, upcoming appointments,
 * recent forum activity, and unread notification count.
 */
class ExpertDashboardController extends Controller
{
    public function __construct(
        private readonly ExpertAppointmentService  $appointmentService,
        private readonly NotificationCenterService $notificationService,
        private readonly ExpertForumService        $forumService,
    ) {}

    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user   = auth('expert')->user();
        $expert = $user->expert()->with(['profile', 'specializations'])->firstOrFail();

        $stats         = $this->appointmentService->getStats($expert);
        $upcoming      = $this->appointmentService->listForExpert($expert, ['status' => Appointment::STATUS_CONFIRMED]);
        $requested     = $this->appointmentService->listForExpert($expert, ['status' => Appointment::STATUS_RESCHEDULE_REQUESTED]);
        $unreadCount   = $this->notificationService->unreadCountForExpert($expert);
        $recentReplies = $this->forumService->getExpertReplies($expert);

        $unifiedSummary = [

            ['label' => 'Total appointments', 'value' => $stats['total'] ?? 0, 'icon' => 'fas fa-calendar-check'],
            ['label' => 'Pending requests', 'value' => $stats['pending'] ?? 0, 'icon' => 'fas fa-hourglass-half'],
            ['label' => 'Completed', 'value' => $stats['completed'] ?? 0, 'icon' => 'fas fa-check-circle'],
        ];

        $unifiedRecentActivity = PlatformActivity::with('actor')
            ->latest('created_at')
            ->where(function ($q) use ($user, $expert) {
                $q->where('actor_user_id', $user->id)
                  ->orWhere('context->expert_id', $expert->id);
            })
            ->limit(8)
            ->get()
            ->map(fn ($entry) => [
                'time' => $entry->created_at?->format('d M, h:i A'),
                'title' => str($entry->action)->replace('.', ' ')->title()->toString(),
                'meta' => ($entry->actor?->name ?? ($entry->actor_role ?? 'system')) . ' • ' . ($entry->entity_type ?? 'n/a'),
            ])
            ->values()
            ->all();

        $unifiedPendingActions = [
            ['label' => 'Pending approvals', 'count' => (int) ($stats['pending'] ?? 0), 'href' => route('expert.appointments.index')],
            ['label' => 'Reschedule requests', 'count' => $requested->total(), 'href' => route('expert.appointments.index', ['status' => Appointment::STATUS_RESCHEDULE_REQUESTED])],
            ['label' => 'Unread notifications', 'count' => (int) $unreadCount, 'href' => route('expert.notifications.index')],
            ['label' => 'Upcoming appointments', 'count' => (int) ($stats['upcoming'] ?? 0), 'href' => route('expert.appointments.index', ['status' => Appointment::STATUS_CONFIRMED])],
        ];

        return view('expert.dashboard', compact(
            'expert', 'stats', 'upcoming', 'requested', 'unreadCount', 'recentReplies',
            'unifiedSummary', 'unifiedRecentActivity', 'unifiedPendingActions'
        ));
    }
}
