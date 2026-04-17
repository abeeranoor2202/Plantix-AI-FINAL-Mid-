<?php

namespace App\Listeners\Expert;

use App\Events\Expert\ExpertStatusChanged;
use App\Models\Expert;
use App\Services\Notifications\NotificationCenterService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendExpertStatusSystemNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function __construct(
        private readonly NotificationCenterService $notifications
    ) {}

    public function handle(ExpertStatusChanged $event): void
    {
        $expert = $event->expert;

        if (! $expert instanceof Expert) {
            return;
        }

        if ($event->status === Expert::STATUS_APPROVED) {
            $this->notifications->notifyExpert(
                $expert,
                'system.profile_approved',
                'Profile approved',
                'Your expert profile has been approved and is now active.',
                ['action_url' => route('expert.dashboard')],
                $expert->user_id,
                route('expert.dashboard')
            );

            return;
        }

        if (in_array($event->status, [Expert::STATUS_SUSPENDED, Expert::STATUS_REJECTED, Expert::STATUS_INACTIVE], true)) {
            $this->notifications->notifyExpert(
                $expert,
                'system.account_warning',
                'Account warning',
                $event->reason ?: 'Your expert account status has changed to ' . str_replace('_', ' ', $event->status) . '.',
                ['action_url' => route('expert.profile.show')],
                $expert->user_id,
                route('expert.profile.show')
            );
        }
    }
}
