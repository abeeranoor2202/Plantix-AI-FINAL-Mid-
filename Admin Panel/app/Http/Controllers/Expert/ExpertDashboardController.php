<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Expert;
use App\Services\Expert\ExpertAppointmentService;
use App\Services\Expert\ExpertNotificationService;
use App\Services\Expert\ExpertForumService;
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
        private readonly ExpertNotificationService $notificationService,
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
        $unreadCount   = $this->notificationService->unreadCount($expert);
        $recentReplies = $this->forumService->getExpertReplies($expert);

        return view('expert.dashboard', compact(
            'expert', 'stats', 'upcoming', 'requested', 'unreadCount', 'recentReplies'
        ));
    }
}
