<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Notification;
use App\Models\Order;
use App\Models\PlatformActivity;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth('web')->user();

        $totalOrders = Order::forCustomer($user->id)->count();
        $activeAppointments = Appointment::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [
                Appointment::STATUS_PENDING_PAYMENT,
                Appointment::STATUS_PENDING_EXPERT_APPROVAL,
                Appointment::STATUS_CONFIRMED,
                Appointment::STATUS_RESCHEDULE_REQUESTED,
                Appointment::STATUS_RESCHEDULED,
            ])
            ->count();
        $recentForumActivity = $user->forumThreads()->count() + $user->forumReplies()->count();
        $notificationSummary = Notification::forUser($user->id, 'user')->count();
        $unreadNotifications = Notification::forUser($user->id, 'user')->unread()->count();

        $summaryCards = [
            ['label' => 'Total Orders', 'value' => $totalOrders, 'icon' => 'fas fa-box', 'href' => route('orders'), 'hint' => 'View order history'],
            ['label' => 'Active Appointments', 'value' => $activeAppointments, 'icon' => 'fas fa-calendar-check', 'href' => route('appointments'), 'hint' => 'Upcoming consultations'],
            ['label' => 'Forum Activity', 'value' => $recentForumActivity, 'icon' => 'fas fa-comments', 'href' => route('forum'), 'hint' => 'Threads and replies'],
            ['label' => 'Notifications', 'value' => $unreadNotifications . ' unread', 'icon' => 'fas fa-bell', 'href' => route('notifications.index'), 'hint' => $notificationSummary . ' total received'],
        ];

        $recentActivity = PlatformActivity::query()
            ->where(function ($query) use ($user): void {
                $query->where('actor_user_id', $user->id)
                    ->orWhere('context->user_id', $user->id)
                    ->orWhere('context->buyer_id', $user->id);
            })
            ->latest('created_at')
            ->limit(8)
            ->get()
            ->map(function (PlatformActivity $entry) {
                return [
                    'time' => $entry->created_at?->format('d M, h:i A'),
                    'title' => str($entry->action)->replace('.', ' ')->title()->toString(),
                    'meta' => ($entry->actor_role ?? 'system') . ' • ' . ($entry->entity_type ?? 'activity'),
                ];
            })
            ->values()
            ->all();

        $pendingActions = [];

        if ($unreadNotifications > 0) {
            $pendingActions[] = ['label' => 'Unread notifications', 'count' => $unreadNotifications, 'href' => route('notifications.index')];
        }

        $pendingDisputes = Order::forCustomer($user->id)->whereIn('dispute_status', ['pending', 'vendor_responded', 'escalated'])->count();
        if ($pendingDisputes > 0) {
            $pendingActions[] = ['label' => 'Open disputes', 'count' => $pendingDisputes, 'href' => route('orders', ['dispute_status' => 'pending'])];
        }

        $pendingAppointments = Appointment::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [Appointment::STATUS_PENDING_PAYMENT, Appointment::STATUS_PENDING_EXPERT_APPROVAL])
            ->count();
        if ($pendingAppointments > 0) {
            $pendingActions[] = ['label' => 'Appointments pending', 'count' => $pendingAppointments, 'href' => route('appointments')];
        }

        return view('customer.dashboard', compact(
            'summaryCards',
            'recentActivity',
            'pendingActions'
        ));
    }
}