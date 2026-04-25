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

        // Only fire for status transitions triggered by the Payout model directly
        // (not for payout requests — those are handled by AdminPayoutRequestController
        //  which creates the notification directly, scoped to the specific expert).
        // We still handle the 'paid' case here as a safety net for auto-settlements.
        if ($status === 'paid') {
            $this->notifications->notifyExpert(
                $expert,
                'payout.processed',
                'Payment Processed — PKR ' . number_format((float) $payout->net_amount, 2),
                'Your payout of PKR ' . number_format((float) $payout->net_amount, 2) . ' (net) has been transferred to your Stripe account.',
                [
                    'payout_id'  => $payout->id,
                    'net_amount' => $payout->net_amount,
                    'action_url' => route('expert.payouts.index'),
                ],
                $expert->user_id,
                route('expert.payouts.index')
            );
        }
    }
}
