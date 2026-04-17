<?php

namespace App\Listeners\Expert;

use App\Events\Expert\ExpertPayoutStatusChanged;
use App\Services\Notifications\NotificationCenterService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendExpertPayoutNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function __construct(
        private readonly NotificationCenterService $notifications
    ) {}

    public function handle(ExpertPayoutStatusChanged $event): void
    {
        $payout = $event->payout->loadMissing('expert');
        $expert = $payout->expert;

        if (! $expert) {
            return;
        }

        $status = (string) ($event->toStatus ?? $payout->status);

        if ($status === 'paid') {
            $this->notifications->notifyExpert(
                $expert,
                'payout.processed',
                'Payout processed',
                'A payout of ' . number_format((float) $payout->net_amount, 2) . ' has been processed.',
                [
                    'payout_id' => $payout->id,
                    'action_url' => route('expert.payouts.index'),
                ],
                $expert->user_id,
                route('expert.payouts.index')
            );

            return;
        }

        if (in_array($status, ['pending', 'processing'], true)) {
            $this->notifications->notifyExpert(
                $expert,
                'payout.pending',
                'Payment pending',
                'Your payout is pending and will be processed shortly.',
                [
                    'payout_id' => $payout->id,
                    'action_url' => route('expert.payouts.index'),
                ],
                $expert->user_id,
                route('expert.payouts.index')
            );
        }
    }
}
