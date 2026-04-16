<?php

namespace App\Listeners\Expert;

use App\Events\Expert\ExpertStatusChanged;
use App\Models\Expert;
use App\Services\Expert\ExpertNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendExpertStatusSystemNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function __construct(
        private readonly ExpertNotificationService $notifications
    ) {}

    public function handle(ExpertStatusChanged $event): void
    {
        $expert = $event->expert;

        if (! $expert instanceof Expert) {
            return;
        }

        if ($event->status === Expert::STATUS_APPROVED) {
            $this->notifications->notify(
                $expert,
                ExpertNotificationService::TYPE_SYSTEM_PROFILE_APPROVED,
                'Profile approved',
                'Your expert profile has been approved and is now active.',
                ['action_url' => route('expert.dashboard')],
                $expert->user_id,
                route('expert.dashboard')
            );

            return;
        }

        if (in_array($event->status, [Expert::STATUS_SUSPENDED, Expert::STATUS_REJECTED, Expert::STATUS_INACTIVE], true)) {
            $this->notifications->notify(
                $expert,
                ExpertNotificationService::TYPE_SYSTEM_ACCOUNT_WARNING,
                'Account warning',
                $event->reason ?: 'Your expert account status has changed to ' . str_replace('_', ' ', $event->status) . '.',
                ['action_url' => route('expert.profile.show')],
                $expert->user_id,
                route('expert.profile.show')
            );
        }
    }
}
